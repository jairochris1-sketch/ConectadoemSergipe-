<?php

namespace App\Services;

class VideoDurationService
{
    public function seconds(string $path): ?float
    {
        $handle = @fopen($path, 'rb');
        if (! $handle) {
            return null;
        }

        try {
            $fileSize = filesize($path);
            $offset = 0;

            while ($offset + 8 <= $fileSize) {
                $atom = $this->readAtomHeader($handle, $offset, $fileSize);
                if (! $atom) {
                    break;
                }

                if ($atom['type'] === 'moov') {
                    return $this->durationFromMovieAtom(
                        $handle,
                        $offset + $atom['header_size'],
                        $atom['size'] - $atom['header_size']
                    );
                }

                $offset += $atom['size'];
            }

            return null;
        } finally {
            fclose($handle);
        }
    }

    private function durationFromMovieAtom($handle, int $offset, int $length): ?float
    {
        $end = $offset + $length;

        while ($offset + 8 <= $end) {
            $atom = $this->readAtomHeader($handle, $offset, $end);
            if (! $atom) {
                break;
            }

            if ($atom['type'] === 'mvhd') {
                fseek($handle, $offset + $atom['header_size']);
                $payload = fread($handle, min(40, $atom['size'] - $atom['header_size']));
                if ($payload === false || strlen($payload) < 20) {
                    return null;
                }

                $version = ord($payload[0]);
                if ($version === 1 && strlen($payload) >= 32) {
                    $timescale = $this->uint32(substr($payload, 20, 4));
                    $duration = $this->uint64(substr($payload, 24, 8));
                } else {
                    $timescale = $this->uint32(substr($payload, 12, 4));
                    $duration = $this->uint32(substr($payload, 16, 4));
                }

                return $timescale > 0 ? $duration / $timescale : null;
            }

            $offset += $atom['size'];
        }

        return null;
    }

    private function readAtomHeader($handle, int $offset, int $limit): ?array
    {
        fseek($handle, $offset);
        $header = fread($handle, 8);
        if ($header === false || strlen($header) !== 8) {
            return null;
        }

        $size = $this->uint32(substr($header, 0, 4));
        $type = substr($header, 4, 4);
        $headerSize = 8;

        if ($size === 1) {
            $extended = fread($handle, 8);
            if ($extended === false || strlen($extended) !== 8) {
                return null;
            }
            $size = $this->uint64($extended);
            $headerSize = 16;
        } elseif ($size === 0) {
            $size = $limit - $offset;
        }

        if ($size < $headerSize || $offset + $size > $limit) {
            return null;
        }

        return ['size' => (int) $size, 'type' => $type, 'header_size' => $headerSize];
    }

    private function uint32(string $bytes): int
    {
        return (int) (unpack('Nvalue', $bytes)['value'] ?? 0);
    }

    private function uint64(string $bytes): float
    {
        $parts = unpack('Nhigh/Nlow', $bytes);

        return (($parts['high'] ?? 0) * 4294967296) + ($parts['low'] ?? 0);
    }
}
