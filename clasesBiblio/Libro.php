<?php

namespace clases;

class Libro extends Documento
{
    private $anoPublicacion;

    /**
     * @param $anoPublicacion
     */
    public function __construct($titulo = null, $codigo = null, $anoPublicacion = 0)
    {
        parent::__construct($codigo, $titulo);
        $this->anoPublicacion = $anoPublicacion;
    }


    public function getAnoPublicacion()
    {
        return $this->anoPublicacion;
    }

    public function setAnoPublicacion($anoPublicacion)
    {
        $this->anoPublicacion = $anoPublicacion;
    }

    public function toString()
    {
        return parent::toString() . "Año de publicacion $this->anoPublicacion";
    }


}