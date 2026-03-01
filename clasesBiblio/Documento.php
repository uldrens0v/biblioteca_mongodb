<?php

namespace clases;

class Documento
{
    private $codigo;
    private $titulo;
    private $prestadoA;


    public function __construct($codigo = null, $titulo = null)
    {
        $this->codigo = $codigo;
        $this->titulo = $titulo;
    }

    public function estaPrestado()
    {
        return $this->prestadoA !== null;
    }

    public function prestaAUsuario(Usuario $user)
    {
        if ($this->prestadoA !== null) {
            echo "Prestado a " . $this->prestadoA->getNombre() . "\n";
        } else {
            $this->prestadoA = $user;
            $user->anadeDocumentoPrestado($this);
        }
    }

    public function devuelve()
    {
        if ($this->prestadoA !== null) {
            $this->prestadoA->eliminaDocumentoPrestado($this->codigo);
            $this->prestadoA = null;
        }
    }

    public function plazoPrestamo()
    {
        if ($this->prestadoA !== null) {
            return $this->prestadoA->plazoPrestamoDocumento();
        }
        return -1;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    public function getTitulo()
    {
        return $this->titulo;
    }

    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    public function getPrestadoA()
    {
        return $this->prestadoA;
    }

    public function setPrestadoA(Usuario $prestadoA)
    {
        $this->prestadoA = $prestadoA;
    }

    public function toString()
    {
        $result = "Código: " . $this->codigo . " Título: " . $this->titulo;
        if ($this->prestadoA !== null) {
            $result .= " Prestado a: " . $this->prestadoA->getNombre();
        }
        return $result . "\n";
    }
}
