<?php

namespace Tests\Unit;

use App\Services\LegacySqlDumpParser;
use PHPUnit\Framework\TestCase;

class LegacySqlDumpParserTest extends TestCase
{
    public function test_it_parses_multiline_mysql_inserts_with_escaped_content(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'legacy-dump-');
        file_put_contents($path, <<<'SQL'
-- dump
INSERT INTO `clientes` (`id`, `nome`, `email`, `descricao`) VALUES
(1, 'José', NULL, 'Texto com vírgula, ponto e vírgula; e parênteses (ok)'),
(2, 'D''Ávila', 'teste@example.com', 'Linha\r\nseguinte');
SQL);

        try {
            $tables = (new LegacySqlDumpParser())->parse($path);
        } finally {
            @unlink($path);
        }

        $this->assertCount(2, $tables['clientes']);
        $this->assertSame('José', $tables['clientes'][0]['nome']);
        $this->assertNull($tables['clientes'][0]['email']);
        $this->assertSame('D\'Ávila', $tables['clientes'][1]['nome']);
        $this->assertSame("Linha\r\nseguinte", $tables['clientes'][1]['descricao']);
    }
}
