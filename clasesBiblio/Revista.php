<?php

namespace clases;

class Revista extends Documento
{

    public function __construct($codigo = null, $titulo = null)
    {
        parent::__construct($codigo, $titulo);
    }

    public function plazoPrestamo()
    {
        return (int)(parent::plazoPrestamo() / 3);
    }
}
