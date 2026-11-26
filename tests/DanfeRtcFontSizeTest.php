<?php

namespace NFePHP\DA\Tests;

use NFePHP\DA\NFe\DanfeRtc;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Parser;

class DanfeRtcFontSizeTest extends TestCase
{
    public function testFontePadraoContinuaComSeisPontos(): void
    {
        $danfe = new DanfeRtc($this->xml());

        $this->assertSame(6.0, $this->fonteDoPrimeiroProduto($danfe->render()));
    }

    public function testSetterAumentaAFonteERefazPdfJaRenderizado(): void
    {
        $danfe = new DanfeRtc($this->xml());
        $this->assertSame(6.0, $this->fonteDoPrimeiroProduto($danfe->render()));

        $this->assertSame($danfe, $danfe->setItemFontSize(8));
        $this->assertSame(8.0, $this->fonteDoPrimeiroProduto($danfe->render()));

        $danfe->setItemFontSize(7.5);
        $this->assertSame(7.5, $this->fonteDoPrimeiroProduto($danfe->render()));
    }

    public function testFonteMaiorPaginaSemPerderItensNasDuasOrientacoes(): void
    {
        foreach (['1', '2'] as $orientacao) {
            $xml = $this->xml($orientacao);
            $paginasOriginais = (new Parser())->parseContent((new DanfeRtc($xml))->render())->getPages();
            $danfe = new DanfeRtc($xml);
            $danfe->setItemFontSize(8);
            $pdf = (new Parser())->parseContent($danfe->render());
            $paginas = $pdf->getPages();

            $this->assertGreaterThan(count($paginasOriginais), count($paginas));
            preg_match_all('/FONTE(\d{3})\s*-/', $pdf->getText(), $itens);
            $this->assertSame(array_map(static function ($i) {
                return sprintf('%03d', $i);
            }, range(1, 40)), $itens[1]);
            foreach ($paginas as $i => $pagina) {
                $this->assertStringContainsString('Folha ' . ($i + 1) . '/' . count($paginas), $pagina->getText());
            }
        }
    }

    public function testRejeitaTamanhoInvalido(): void
    {
        $danfe = new DanfeRtc($this->xml());
        foreach ([0, -1, INF, NAN] as $tamanho) {
            try {
                $danfe->setItemFontSize($tamanho);
                $this->fail('Deveria rejeitar um tamanho que não seja positivo e finito.');
            } catch (\InvalidArgumentException $exception) {
                $this->assertNotEmpty($exception->getMessage());
            }
        }
    }

    public function testInformacoesAdicionaisLongasContinuamCompletasComOitoPontos(): void
    {
        $doc = new \DOMDocument();
        $doc->loadXML($this->xml());
        $item = $doc->getElementsByTagName('det')->item(0);
        $info = $doc->createElementNS($item->namespaceURI, 'infAdProd');
        $identificadores = array_map(static function ($i) {
            return '3512345000000' . $i;
        }, range(10, 23));
        $info->nodeValue = implode('; ', $identificadores);
        $item->insertBefore($info, $item->getElementsByTagName('vItem')->item(0));
        $danfe = new DanfeRtc($doc->saveXML());
        $danfe->setItemFontSize(8);

        $texto = (new Parser())->parseContent($danfe->render())->getText();

        foreach ($identificadores as $identificador) {
            $this->assertStringContainsString($identificador, $texto);
        }
        $linhas = array_filter(explode("\n", $texto), static function ($linha) {
            return strpos($linha, '35123450000') !== false;
        });
        $this->assertGreaterThan(1, count($linhas));
    }

    private function fonteDoPrimeiroProduto(string $conteudo): float
    {
        $pdf = (new Parser())->parseContent($conteudo);
        foreach ($pdf->getPages() as $pagina) {
            preg_match_all(
                '/\/F\d+ ([\d.]+) Tf|\(([^\r\n]*)\) Tj/',
                $pagina->get('Contents')->getContent(),
                $comandos,
                PREG_SET_ORDER
            );
            $tamanho = 0.0;
            foreach ($comandos as $comando) {
                if ($comando[1] !== '') {
                    $tamanho = (float)$comando[1];
                }
                if (isset($comando[2]) && strpos($comando[2], 'FONTE001 -') !== false) {
                    return $tamanho;
                }
            }
        }
        $this->fail('O primeiro produto não foi encontrado no PDF.');
    }

    private function xml(string $orientacao = '1'): string
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/fixtures/xml/nfe_rtc_itens.xml');
        $doc->getElementsByTagName('tpImp')->item(0)->nodeValue = $orientacao;
        $inf = $doc->getElementsByTagName('infNFe')->item(0);
        $originais = iterator_to_array($doc->getElementsByTagName('det'));
        $depois = end($originais)->nextSibling;
        foreach ($originais as $item) {
            $inf->removeChild($item);
        }
        for ($i = 1; $i <= 40; $i++) {
            $item = $originais[($i - 1) % count($originais)]->cloneNode(true);
            $item->setAttribute('nItem', (string)$i);
            $item->getElementsByTagName('cProd')->item(0)->nodeValue = sprintf('FONTE%03d', $i);
            $item->getElementsByTagName('xProd')->item(0)->nodeValue = 'PRODUTO PARA TESTAR FONTE E QUEBRA DE LINHAS';
            $inf->insertBefore($item, $depois);
        }

        return $doc->saveXML();
    }
}
