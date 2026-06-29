<?php

namespace NFePHP\DA\NFe\Traits;

/**
 * Bloco itens da NFe
 */
trait TraitBlocoIII
{
    protected function getDescontoUnitarioItem(\DOMElement $prod): string
    {
        $vDesc = (float)$this->getTagValue($prod, "vDesc");
        $qCom = (float)$this->getTagValue($prod, "qCom");

        if ($vDesc <= 0 || $qCom <= 0) {
            return '0,00';
        }

        return number_format(round($vDesc / $qCom, 2), 2, ",", ".");
    }

    protected function getValorTotalItem(\DOMElement $prod): string
    {
        $vProd = (float)$this->getTagValue($prod, "vProd");
        $vDesc = (float)$this->getTagValue($prod, "vDesc");
        $valorTotal = $vProd - $vDesc;

        return number_format($valorTotal, 2, ",", ".");
    }

    protected function blocoIII($y)
    {
        if ($this->flagResume) {
            return $y;
        }
        $matrix = [0.08, 0.39, 0.06, 0.06, 0.10, 0.10, 0.21];
        $fsize = 7;
        if ($this->paperwidth < 70) {
            $fsize = 5;
        }
        $aFont = ['font'=> $this->fontePadrao, 'size' => $fsize, 'style' => ''];

        $texto = "Codigo";
        $x = $this->margem;
        $this->pdf->textBox($x, $y, ($this->wPrint * $matrix[0]), 3, $texto, $aFont, 'T', 'L', false, '', true);

        $texto = "Descricao";
        $x1 = $x + ($this->wPrint * $matrix[0]);
        $this->pdf->textBox($x1, $y, ($this->wPrint * $matrix[1]), 3, $texto, $aFont, 'T', 'L', false, '', true);

        $texto = "Qtde";
        $x2 = $x1 + ($this->wPrint * $matrix[1]);
        $this->pdf->textBox($x2, $y, ($this->wPrint * $matrix[2]), 3, $texto, $aFont, 'T', 'C', false, '', true);

        $texto = "UN";
        $x3 = $x2 + ($this->wPrint * $matrix[2]);
        $this->pdf->textBox($x3, $y, ($this->wPrint * $matrix[3]), 3, $texto, $aFont, 'T', 'C', false, '', true);

        $texto = "V.Unit";
        $x4 = $x3 + ($this->wPrint * $matrix[3]);
        $this->pdf->textBox($x4, $y, ($this->wPrint * $matrix[4]), 3, $texto, $aFont, 'T', 'C', false, '', true);

        $texto = "Desc.";
        $x5 = $x4 + ($this->wPrint * $matrix[4]);
        $this->pdf->textBox($x5, $y, ($this->wPrint * $matrix[5]), 3, $texto, $aFont, 'T', 'C', false, '', true);

        $texto = "Vl Total";
        $x6 = $x5 + ($this->wPrint * $matrix[5]);
        $y1 = $this->pdf->textBox($x6, $y, ($this->wPrint * $matrix[6]), 3, $texto, $aFont, 'T', 'R', false, '', true);

        $y2 = $y + $y1;
        if ($this->det->length == 0) {
        } else {
            foreach ($this->itens as $item) {
                $it = (object) $item;
                $this->pdf->textBox(
                    $x,
                    $y2,
                    ($this->wPrint * $matrix[0]),
                    $it->height,
                    $it->codigo,
                    $aFont,
                    'T',
                    'L',
                    false,
                    '',
                    true
                );
                $this->pdf->textBox(
                    $x1,
                    $y2,
                    ($this->wPrint * $matrix[1]),
                    $it->height,
                    $it->desc,
                    $aFont,
                    'T',
                    'L',
                    false,
                    '',
                    false
                );
                $this->pdf->textBox(
                    $x2,
                    $y2,
                    ($this->wPrint * $matrix[2]),
                    $it->height,
                    $it->qtd,
                    $aFont,
                    'T',
                    'R',
                    false,
                    '',
                    true
                );
                $this->pdf->textBox(
                    $x3,
                    $y2,
                    ($this->wPrint * $matrix[3]),
                    $it->height,
                    $it->un,
                    $aFont,
                    'T',
                    'C',
                    false,
                    '',
                    true
                );
                $this->pdf->textBox(
                    $x4,
                    $y2,
                    ($this->wPrint * $matrix[4]),
                    $it->height,
                    $it->vunit,
                    $aFont,
                    'T',
                    'R',
                    false,
                    '',
                    true
                );
                $this->pdf->textBox(
                    $x5,
                    $y2,
                    ($this->wPrint * $matrix[5]),
                    $it->height,
                    $it->desconto_unitario,
                    $aFont,
                    'T',
                    'R',
                    false,
                    '',
                    true
                );
                $this->pdf->textBox(
                    $x6,
                    $y2,
                    ($this->wPrint * $matrix[6]),
                    $it->height,
                    $it->valor,
                    $aFont,
                    'T',
                    'R',
                    false,
                    '',
                    true
                );
                $y2 += $it->height;
            }
        }
        $this->pdf->dashedHLine($this->margem, $this->bloco3H + $y, $this->wPrint, 0.1, 30);

        return $this->bloco3H + $y;
    }

    protected function calculateHeightItens($descriptionWidth)
    {
        if ($this->flagResume) {
            return 0;
        }
        $fsize = 7;
        if ($this->paperwidth < 70) {
            $fsize = 5;
        }
        $hfont = (imagefontheight($fsize) / 72) * 15;
        $aFont = ['font'=> $this->fontePadrao, 'size' => $fsize, 'style' => ''];
        $htot = 0;
        if ($this->det->length == 0) {
        } else {
            foreach ($this->det as $item) {
                $prod = $item->getElementsByTagName("prod")->item(0);
                $cProd = $this->getTagValue($prod, "cProd");
                $xProd = substr($this->getTagValue($prod, "xProd"), 0, 45);
                $qCom = (float)$this->getTagValue($prod, "qCom");
                $uCom = $this->getTagValue($prod, "uCom");
                $vUnCom = number_format((float)$this->getTagValue($prod, "vUnCom"), 2, ",", ".");
                $vProd = $this->getValorTotalItem($prod);
                $descontoUnitario = $this->getDescontoUnitarioItem($prod);

                $tempPDF = new \NFePHP\DA\Legacy\Pdf();
                $tempPDF->setFont($this->fontePadrao, '', $fsize);

                $n = $tempPDF->wordWrap($xProd, $descriptionWidth);
                $limit = 45;
                while ($n > 2) {
                    $limit -= 1;
                    $xProd = substr($this->getTagValue($prod, "xProd"), 0, $limit);
                    $p = $xProd;
                    $n = $tempPDF->wordWrap($p, $descriptionWidth);
                }
                $h = ($hfont * $n) + 1.2;
                $this->itens[] = [
                    "codigo" => $cProd,
                    "desc" => $xProd,
                    "qtd" => $qCom,
                    "un" => $uCom,
                    "vunit" => $vUnCom,
                    "desconto_unitario" => $descontoUnitario,
                    "valor" => $vProd,
                    "height" => $h
                ];
                $htot += $h;
            }
        }

        return $htot + 2;
    }
}
