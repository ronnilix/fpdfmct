<?php

/**
 * FPDFMC - FPDF Multicell Support for creating a table row
 *
 * MultiCell support for creating table with FPDF library
 * also support for putting text as utf8 - text will be converted to cp1252 then
 *
 * @category Library
 * @package fpdf
 * @author Ronald Simonek <ronni@ronnilix.eu>
 * @license MIT
 * @version 0.2
 * @uses setasign/fpdf
 */
class fpdfmct extends FPDF
{
	protected $utf8 = false;
	protected $mcellh = 0;

	/**
	 * set utf8 to true when you using utf8 for text
	 *
	 * @param bool $utf8
	 */
	public function setUTF8 ($utf8)
	{
		$this->utf8 = $utf8;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see FPDF::Cell()
	 */
	function Cell ($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
	{
		if( $txt !== '' && $this->utf8 )
		{
			// converting back to cp1252, when used utf8
			$txt = iconv ( 'utf-8', 'cp1252', $txt );
		}
		parent::Cell ( $w, $h, $txt, $border, $ln, $align, $fill, $link );
	}

	/**
	 * for multicell check pagebreak
	 *
	 * @param int $h
	 *        	hight of cell
	 */
	protected function CheckPageBreak ($h)
	{
		// If the height h would cause an overflow, add a new page immediately
		if( $this->GetY () + $h > $this->PageBreakTrigger )
			$this->AddPage ();
	}

	/**
	 * calculates the number of lines a MultiCell of width w will take
	 *
	 * @param int $w
	 *        	width of cell
	 * @param string $txt
	 *        	text for output into the cell
	 */
	protected function NbLines ($w, $txt)
	{
		$cw = &$this->CurrentFont['cw'];
		if( $w == 0 ) // bei 0 ist die Länge von der aktuellen Position bis Zeilenende
			$w = $this->w - $this->rMargin - $this->x;
		$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize; // maximale Zellenbreite je Zeile
		$s = str_replace ( "\r", '', $txt ); // entfernt "carriage return" ( von Windows für Zeilenumbruch )
		$nb = strlen ( $s );
		if( $nb > 0 && $s[$nb - 1] == "\n" )
		{
			$nb--;
		}
		$sep = -1; // Stelle für Trennzeichen (Leerzeichen) pro Zeile. Wenn "-1" dann wurde keines gefunden.
		$i = 0; // Zähler für Zeichenstelle
		$j = 0; // Stelle seit letztem Zeilenumbruch
		$l = 0; // Zeilenlänge für Zeichen. Damit wird geprüft ob umgebrochen werden muss.
		$nl = 0; // Anzahl benötigter Zeilen
		if( $nb == 0 )
		{
			$nl = 1;
		}
		while( $i < $nb )
		{
			// Get next character
			$c = $s[$i];
			if( $c == "\n" )
			{
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl++;
				continue;
			}
			if( $c == ' ' )
			{
				$sep = $i;
			}
			$l += $cw[$c];
			if( $l > $wmax )
			{
				if( $sep == -1 )
				{
					if( $i == $j )
					{
						$i++;
					}
				}
				else
				{
					$i = $sep + 1;
				}
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl++;
			}
			else
			{
				$i++;
			}
		}
		if( $i != $j )
		{
			$nl++;
		}
		return $nl;
	}

	/**
	 * MultiCell which draws rectangle in the given width for the Multicell
	 * for last column use width 0
	 *
	 * @param int $w
	 *        	width of column
	 * @param int $h
	 *        	hight of column ( used for Multicell function )
	 * @param int $mh
	 *        	hight of Cell ( used for drawing Rectangle )
	 * @param String $txt
	 *        	Text for output
	 * @param String $align
	 *        	Align of Text
	 * @uses MultiCell
	 *      
	 */
	public function MultiCellTable ($w, $h, $txt, $align = 'J')
	{
		if( $this->mcellh == 0 )
		{
			$lines = $this->NbLines ( $w, $txt );
			$mh = $lines * $h;
			$this->mcellh = $mh;
		}
		else
			$mh = $this->mcellh;
		if( $w == 0 )
		{
			$w = $this->w - $this->rMargin - $this->x;
			$setback = false;
		}
		else
			$setback = true;
		$y = $this->GetY ();
		$x = $this->GetX ();
		$this->Rect ( $x, $y, $w, $mh );
		$this->MultiCell ( $w, $h, $txt, 0, $align );
		if( $setback )
		{
			$this->SetY ( $y );
			$this->SetX ( $x + $w );
		}
		else
		{
			$this->SetY ( $y + $mh );
			$this->mcellh = 0;
		}
	}
}
?>