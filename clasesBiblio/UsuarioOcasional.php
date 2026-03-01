<?php

namespace clases;

class UsuarioOcasional extends Usuario
{
    private const MAX_PRESTAMOS_A_USUARIOS = 2;
    private const LIMITE_PRESTAMO_A_USUARIOS = 2;

    public function __construct($DNI = null, $nombre = null)
    {
        parent::__construct($DNI, $nombre);
    }

    public function __toString()
    {
        return parent::__toString() . "clases\Usuario ocasional";
    }
}