<?php

class Widget
{
    protected $code;

    public function __toString()
    {
        if (!empty($this->code))
            return $this->code;

        return '';
    }
}

class Foutmelding extends Widget
{
    public function __construct(string $tekst)
    {
        $this->code = sprintf('<div class="alert alert-warning">%s</div><br>', $tekst);
    }
}

class NeutraleMelding extends Widget
{
    public function __construct(string $tekst)
    {
        $this->code = sprintf('<div class="alert alert-info">%s</div><br>', $tekst);
    }
}

class GoedeMelding extends Widget
{
    public function __construct(string $tekst)
    {
        $this->code = sprintf('<div class="alert alert-success">%s</div><br>', $tekst);
    }
}

class DagSelector extends Widget
{
	public function __construct(int $geselecteerdeDag, bool $toonLegeOptie = FALSE)
	{
		$this->code = $toonLegeOptie ? '<option value="">&nbsp;</option>' : '';

		for($i = 1; $i <= 31; $i++)
		{
			$i = (strlen($i) == 1) ? $i = "0". $i : $i;
			$sel = ($i == $geselecteerdeDag) ? "selected" : "";
			$this->code .= "<option value='". $i ."' $sel>". $i ."</option>";
		}
	}
}

class MaandSelector extends Widget
{
	public function __construct(int $geselecteerdeMaand, bool $toonLegeOptie = FALSE)
	{
		$this->code = $toonLegeOptie ? '<option value="">&nbsp;</option>' : '';

		for($i = 1; $i <= 12; $i++)
		{
			$label = Util::geefMaand($i);
			$i = (strlen($i) == 1) ? $i = "0". $i : $i;
			$sel = ($i == $geselecteerdeMaand) ? "selected" : "";
			$this->code .= "<option value='". $i ."' $sel>". $label ."</option>";
		}
	}
}

class JaarSelector extends Widget
{
	public function __construct(int $geselecteerdJaar, bool $toonLegeOptie = FALSE)
	{
		$this->code = $toonLegeOptie ? '<option value="">&nbsp;</option>' : '';
		$laatsteWaarde = ($geselecteerdJaar >= 1900) ? min($geselecteerdJaar, date("Y")-36) : date("Y")-36;

		for($i = date("Y")-5; $i >= $laatsteWaarde; $i--)
		{
			$i = (strlen($i) == 1) ? $i = "0". $i : $i;
			$sel = ($i == $geselecteerdJaar) ? "selected" : "";
			$this->code .= "<option value='". $i ."' $sel>". $i ."</option>";
		}
	}
}

class PaginaMenu extends Widget
{
	public function __construct($menuArray)
	{
		$this->code = '<div class="topicmenu"><div class="btn-group" role="group">';

		foreach($menuArray as $menuItem)
		{
			if ($menuItem == 'separator')
				$this->code .= '</div> <div class="btn-group" role="group">';
			elseif (!empty($menuItem['link']))
				$this->code .= sprintf('<a role="button" class="btn btn-default" title="%s" href="%s">%s</a>', $menuItem['tooltip'], $menuItem['link'], $menuItem['opschrift']);
			elseif (!empty($menuItem['id']))
				$this->code .= sprintf('<button class="btn btn-default" id="%s" title="%s">%s</button>', $menuItem['id'], $menuItem['tooltip'], $menuItem['opschrift']);
		}

		$this->code .= '</div></div>';
	}

}

class Paginering extends Widget
{
	public function __construct($link, $aantalPaginas, $huidigePagina, $verschuiving = 0)
	{
		if ($aantalPaginas == 1)
			return;

		$this->code = '<div class="lettermenu"><ul class="pagination">';
		$teTonenPaginas = [
			1, 2, 3,
			$aantalPaginas, $aantalPaginas - 1, $aantalPaginas - 2,
			$huidigePagina - 2, $huidigePagina - 1, $huidigePagina, $huidigePagina + 1, $huidigePagina + 2
		];

		if ($huidigePagina == 7)
		{
			$teTonenPaginas[] = 4;
		}
		if ($aantalPaginas - $huidigePagina == 6)
		{
			$teTonenPaginas[] = $aantalPaginas - 3;
		}

		$teTonenPaginas = array_unique($teTonenPaginas);
		natsort($teTonenPaginas);

		$vorigePaginanummer = 0;
		foreach ($teTonenPaginas as $i)
		{
			if ($i > $aantalPaginas)
				break;

			if ($i < 1)
				continue;

			if ($vorigePaginanummer != $i - 1)
				$this->code .= '<li><span>...</span></li>';

			$class = '';
			if($i == $huidigePagina)
				$class = 'class="active"';

			$this->code .= sprintf('<li %s><a href="%s%d">%d</a></li>', $class, $link, ($i + $verschuiving), $i);

			$vorigePaginanummer = $i;
		}

		$this->code .= '</ul></div>';
	}
}

class PaginaTabs extends Widget
{
	public function __construct($paginaArray, $urlPrefix = '', $huidigePagina = '')
	{
		$this->code = '<ul class="nav nav-tabs">';

		foreach($paginaArray as $link => $titel)
		{
			$class = ($link == $huidigePagina) ? ' class="active"' : '';

			$this->code .= '<li role="presentation"' . $class . '><a class="nav-link" href="' . rtrim($urlPrefix . $link, '/') . '">' . $titel . '</a></li>';
		}

		$this->code .= '</ul>';
	}
}
