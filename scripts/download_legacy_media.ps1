param(
    [Parameter(Mandatory = $true)]
    [string]$DumpPath,

    [string]$BaseUrl = 'https://conectadoemsergipe.com',

    [string]$DestinationRoot = 'public/uploads/ads'
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'
Add-Type -AssemblyName System.Net.Http
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$resolvedDump = (Resolve-Path -LiteralPath $DumpPath).Path
$projectRoot = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$destination = [IO.Path]::GetFullPath((Join-Path $projectRoot $DestinationRoot))
$publicRoot = [IO.Path]::GetFullPath((Join-Path $projectRoot 'public'))

if (-not $destination.StartsWith($publicRoot, [StringComparison]::OrdinalIgnoreCase)) {
    throw 'A pasta de destino precisa estar dentro de public.'
}

$sql = [IO.File]::ReadAllText($resolvedDump, [Text.UTF8Encoding]::new($false))
$paths = [regex]::Matches($sql, "uploads/ads/[A-Za-z0-9._/-]+") |
    ForEach-Object { $_.Value.TrimEnd('.') } |
    Sort-Object -Unique

if ($paths.Count -eq 0) {
    throw 'Nenhuma mídia uploads/ads foi encontrada no dump.'
}

New-Item -ItemType Directory -Path $destination -Force | Out-Null

$handler = [Net.Http.HttpClientHandler]::new()
$handler.AutomaticDecompression = [Net.DecompressionMethods]::GZip -bor [Net.DecompressionMethods]::Deflate
$client = [Net.Http.HttpClient]::new($handler)
$client.Timeout = [TimeSpan]::FromSeconds(45)
$client.DefaultRequestHeaders.UserAgent.ParseAdd('ConectadoEmSergipe-ETL/1.0')

$downloaded = 0
$existing = 0
$failed = [Collections.Generic.List[object]]::new()

try {
    foreach ($legacyPath in $paths) {
        $relative = $legacyPath.Substring('uploads/ads/'.Length)
        if ($relative.Contains('..')) {
            $failed.Add([pscustomobject]@{ path = $legacyPath; error = 'invalid path' })
            continue
        }

        $target = [IO.Path]::GetFullPath((Join-Path $destination ($relative -replace '/', [IO.Path]::DirectorySeparatorChar)))
        if (-not $target.StartsWith($destination, [StringComparison]::OrdinalIgnoreCase)) {
            $failed.Add([pscustomobject]@{ path = $legacyPath; error = 'path outside destination' })
            continue
        }

        if ((Test-Path -LiteralPath $target) -and (Get-Item -LiteralPath $target).Length -gt 0) {
            $existing++
            continue
        }

        $parent = Split-Path -Parent $target
        New-Item -ItemType Directory -Path $parent -Force | Out-Null
        $temporary = $target + '.part'

        try {
            $uri = $BaseUrl.TrimEnd('/') + '/' + $legacyPath
            $response = $client.GetAsync($uri, [Net.Http.HttpCompletionOption]::ResponseHeadersRead).GetAwaiter().GetResult()
            if (-not $response.IsSuccessStatusCode) {
                throw "HTTP $([int]$response.StatusCode)"
            }

            $stream = $response.Content.ReadAsStreamAsync().GetAwaiter().GetResult()
            $file = [IO.File]::Open($temporary, [IO.FileMode]::Create, [IO.FileAccess]::Write, [IO.FileShare]::None)
            try {
                $stream.CopyTo($file)
            } finally {
                $file.Dispose()
                $stream.Dispose()
                $response.Dispose()
            }

            if ((Get-Item -LiteralPath $temporary).Length -eq 0) {
                throw 'empty response'
            }

            Move-Item -LiteralPath $temporary -Destination $target -Force
            $downloaded++
        } catch {
            if (Test-Path -LiteralPath $temporary) {
                Remove-Item -LiteralPath $temporary -Force
            }
            $failed.Add([pscustomobject]@{ path = $legacyPath; error = $_.Exception.Message })
        }

        $processed = $downloaded + $existing + $failed.Count
        if ($processed % 50 -eq 0) {
            Write-Output "Processadas $processed de $($paths.Count) mídias..."
        }
    }
} finally {
    $client.Dispose()
    $handler.Dispose()
}

[pscustomobject]@{
    referenced = $paths.Count
    downloaded = $downloaded
    already_existing = $existing
    failed = $failed.Count
    destination = $destination
} | ConvertTo-Json -Depth 3

if ($failed.Count -gt 0) {
    $failed | Select-Object -First 20 | ConvertTo-Json -Depth 3
    exit 2
}
