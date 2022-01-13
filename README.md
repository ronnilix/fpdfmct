# FPDF MultiCell for Table

## Description

This is a library which uses FPDF script, and adds the possibility to make table with MultiCell.

## Installation

composer require ronnilix/fpdfmct

## Usage

with function "NbLines" you calculate the maximum numbers of line for the cells.
Then call function "MulticellTable" with the parameters and output the cell.

example:
```php
$data[0] = "...";
$data[1] = "...";
$pdf = new fpdfmct ();
$pdf->AddPage ();
$pdf->SetFont ( 'Arial', '', 9 );
foreach( $data as $value )
{
	$lines = max ( $lines, $pdf->NbLines ( 40, $value ) );
}

$pdf->MultiCellTable ( 40, 5, $lines, $data[0], "L" );
$pdf->MultiCellTable ( 40, 5, $lines, $data[1], "L" );
$pdf->Output ( "D", "test.pdf" );

```
