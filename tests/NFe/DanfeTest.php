<?php

namespace NFePHP\DA\Tests\NFe;

use NFePHP\DA\NFe\Danfe;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Parser;

class DanfeTest extends TestCase
{
    public function test_gerar_nfe_linha_continua(): void
    {
        $obj = new Danfe(file_get_contents(TEST_FIXTURES . 'xml/nfe.xml'));
        $obj->setUsarLinhaTracejadaSeparacaoItens(false);
        $pdf = $obj->render();
        file_put_contents(TEST_FIXTURES . 'pdf/nfe_linhas.pdf', $pdf);
        $this->assertIsString($pdf);
    }

    public function test_gerar_nfe_exibir_ind_pag_a_prazo(): void
    {
        $obj = new Danfe(file_get_contents(TEST_FIXTURES . 'xml/nfe.xml'));
        $obj->exibirTextoFatura = true;
        $obj->exibirIndPagDuplicatas = true;
        $pdf = $obj->render();

        $parser = new Parser();
        $pdfDocument = $parser->parseContent($pdf);
        $text = $pdfDocument->getText();
        $this->assertStringContainsString(
            'Outros - Á prazo',
            $text
        );
    }
}
