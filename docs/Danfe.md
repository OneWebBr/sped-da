# DaNFe
O **D**ocumento **A**uxiliar da **NF** **E**letrôncica é a impressão de dados para auxiliar o transporte e a fiscalização. 
Constitui-se basicamente uma impressão ou documento visual eletrônico como PDF, que possui dados do emitente, do destinatário um código de barras para leitura da [Chave da NFe]  dados dos produtos da nota, transportadora, e resumo de totais e impostos.
Nele também consta o número de protocolo no registro na integração junto ao sped.

## Class Danfe

# Métodos

### function __construct()
Método construtor. Instancia a classe

    ```php
    $danfe = new Danfe([String xml]);
    ``` 
### function render()
Método de rederização do PDF

```php
$pdf = $danfe->render();
```
retorna um PDF codificado.

## Class DanfeRtc

A classe `DanfeRtc` permite configurar o tamanho da fonte da grade de produtos:

```php
use NFePHP\DA\NFe\DanfeRtc;

$danfe = new DanfeRtc($xml);
$danfe->setItemFontSize(8);
$pdf = $danfe->render();
```

`setItemFontSize(float $fontSize = 6): self` recebe o tamanho em pontos e retorna
o próprio objeto. O padrão continua em **6 pt**; a configuração se aplica à
descrição, informações adicionais, NCM/cClassTrib, CST/CFOP, quantidades, valores
e tributos dos itens. Cabeçalhos e os demais quadros mantêm seus tamanhos próprios.

A altura dos itens e a quebra das linhas acompanham o tamanho escolhido. Fontes
maiores podem gerar mais páginas. O total de páginas considera também a altura
do primeiro item deslocado para a página seguinte.

Tamanhos que não sejam positivos e finitos geram `InvalidArgumentException`.
Chamar o método depois de `render()` com um tamanho diferente faz o próximo
`render()` gerar novamente o documento. Para voltar ao padrão, use
`$danfe->setItemFontSize()`.
