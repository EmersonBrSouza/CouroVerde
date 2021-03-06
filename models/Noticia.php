<?php
namespace models;

/**
 * Classe responsável por representar uma notícia no contexto do sistema.
 * @author MItologhic Software
 *
 */
class Noticia implements \JsonSerializable{
    
    private $idNoticia;
    private $titulo;
    private $subtitulo;
    private $descricao;
    private $caminhoImagem;
    private $data;

    /**
     * Construtor da classe
     * @param unknown $idNoticia id da notícia     
     * @param unknown $titulo titulo da notícia
     * @param unknown $subtitulo subtitulo da notícia
     * @param unknown $descricao descricao da notícia     
     * @param unknown $caminhoImagem caminho da imagem da notícia
     * @param unknown $data data da notícia  
     */
    public function __construct($idNoticia,$titulo, $subtitulo, $descricao, $caminhoImagem, $data) {
        $this->idNoticia = $idNoticia;
        $this->titulo = $titulo;
        $this->subtitulo = $subtitulo;
        $this->descricao = $descricao;
        $this->caminhoImagem = $caminhoImagem;
        $this->data = $data;
    }

    /**
     * Obtém o idNoticia.
     * @return idNoticia
     */
    public function getidNoticia() {
        return $this->idNoticia;
    }

    /**
     * Obtém o titulo da notícia.
     * @return titulo
     */
    public function getTitulo() {
        return $this->titulo;
    }

    /**
     * Configura o titulo da notícia.
     * @param unknown $titulo titulo da notícia
     */
    public function setTitulo($titulo) {
        $this->titulo = $titulo;
    }

    /**
     * Obtém o subtitulo da notícia.
     * @param unknown $subtitulo subtitulo da notícia
     */
    public function getSubtitulo() {
        return $this->subtitulo;
    }

    /**
     * Configura o subtitulo da notícia.
     * @param unknown $subtitulo subtitulo da notícia
     */
    public function setSubtitulo($subtitulo) {
        $this->subtitulo = $subtitulo;
    }

    /**
     * Obtém o caminho da imagem da notícia.
     * @return caminhoImagem
     */
    public function getCaminhoImagem() {
        return $this->caminhoImagem;
    }

    /**
     * Configura o caminho da imagem.
     * @param unknown $caminhoImagem caminho da imagem
     */
    public function setCaminhoImagem($caminhoImagem) {
        $this->caminhoImagem = $caminhoImagem;
    }

    /**
     * Obtém a data da notícia.
     * @return data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Configura a data da notícia.
     * @param unknown $subtitulo data da notícia
     */
    public function setData($data) {
        $this->data = $data;
    }  

    /**
     * Obtém a descrição da notícia.
     * @return descricao
     */
    public function getDescricao() {
        return $this->descricao;
    }

    /**
     * Configura a descricao da notícia.
     * @param unknown $descricao descricao da notícia
     */
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }
    
    /**
     * Define como os dados da classe Noticia serão utilizados na conversão para um json.
     * @return dados da classe em formato .json
     */
    public function jsonSerialize() { 
        return [
            'idNoticia' => $this->idNoticia,
            'titulo' => $this->titulo,
            'subtitulo' => $this->subtitulo,
            'descricao' => $this->descricao,
            'caminhoImagem' => $this->caminhoImagem,
            'data' => $this->data,                                                                                                        
        ];
    }     
}

?>
