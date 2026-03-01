<?php


namespace clases;

class Socio extends Usuario
{
    private const MAX_PRESTAMOS_A_SOCIOS = 20;
    private const LIMITE_PRESTAMO_A_SOCIOS = 30;

    public function __construct($DNI = null, $nombre = null)
    {
        parent::__construct($DNI, $nombre);
    }

    public function __toString()
    {
        return parent::__toString() . "clases\Socio";
    }
}