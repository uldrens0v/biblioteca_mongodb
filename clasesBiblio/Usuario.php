<?php

namespace clases;
class Usuario
{
    private $DNI;
    private $nombre;
    private $presstamos;
    private $maxPrestamos;
    private $limitePrestamos;
    private $numPrestamos;

    /**
     * @param $DNI
     * @param $nombre
     * @param $presstamos
     * @param $maxPrestamos
     * @param $limitePrestamos
     * @param $numPrestamos
     */
    public function __construct($DNI = null, $nombre = null, $presstamos = null, $maxPrestamos = 0, $limitePrestamos = 0, $numPrestamos = 0)
    {
        $this->DNI = $DNI;
        $this->nombre = $nombre;
        $this->presstamos = $presstamos ?? [];
        $this->maxPrestamos = $maxPrestamos;
        $this->limitePrestamos = $limitePrestamos;
        $this->numPrestamos = $numPrestamos;
    }

    public function alcanzadoLimitePrestamos()
    {
        return $this->numPrestamos >= $this->maxPrestamos;
    }

    public function anadeDocumentoPrestado($doc)
    {
        if (!$this->alcanzadoLimitePrestamos()) {
            if (!$doc->estaPrestado()) {
                $this->presstamos[$this->numPrestamos] = $doc;
                $this->numPrestamos++;
            }
        }
    }

    public function eliminaDocumentoPrestado($codigo)
    {
        $pos = $this->buscaDocumentoPrestado($codigo);
        if ($pos != -1) {
            for ($j = $pos; $j < $this->numPrestamos - 1; $j++) {
                $this->presstamos[$j] = $this->presstamos[$j + 1];
            }
            unset($this->presstamos[$this->numPrestamos - 1]);
            $this->numPrestamos--;
        } else {
            echo "El documento con cOdigo " . $codigo . " no esta prestado\n";
        }
    }

    public function buscaDocumentoPrestado($codigo)
    {
        for ($i = 0; $i < $this->numPrestamos; $i++) {
            if ($this->presstamos[$i]->getCodigo() === $codigo) {
                return $i;
            }
        }
        return -1;
    }

    public function getDNI()
    {
        return $this->DNI;
    }

    public function setDNI($DNI)
    {
        $this->DNI = $DNI;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function getMaxPrestamos()
    {
        return $this->maxPrestamos;
    }

    public function setMaxPrestamos($maxPrestamos)
    {
        $this->maxPrestamos = $maxPrestamos;
    }

    public function plazoPrestamoDocumento()
    {
        return $this->limitePrestamos;
    }

    public function setLimitePrestamos($limitePrestamos)
    {
        $this->limitePrestamos = $limitePrestamos;
    }

    public function __toString()
    {
        return "DNI: " . $this->DNI . " Nombre: " . $this->nombre . " Préstamos: " . $this->numPrestamos;
    }
}
