<?php

namespace App\Controllers;

//os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;

class AppController extends Action {


	public function timeline() {

		$this->validaAutenticacao();
		//recuperação dos tweets
		$tweet = Container::getModel('Tweet');

		$tweet->__set('id_usuario', $_SESSION['id']);

		//variáveis de paginação
		$registros_por_pag = 3;
		
		$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

		$deslocamento = ($pagina - 1) * $registros_por_pag;


		//$tweets = $tweet->getAll();
		$tweets = $tweet->getPorPagina($registros_por_pag, $deslocamento);
		$total_tweets = $tweet->getTotalRegistros();
		$total_paginas = ceil($total_tweets['total']/$registros_por_pag);
		$this->view->total_de_paginas = $total_paginas;
		$this->view->pagina_ativa = $pagina;
		$this->view->tweets = $tweets;

		//recuperação de dados
		$usuario = Container::getModel('usuario');

		$usuario->__set('id', $_SESSION['id']);

		$this->view->info_usuario = $usuario->getInfoUsuario();
		$this->view->total_tweets = $usuario->getTotalTweets();
		$this->view->total_seguindo = $usuario->getTotalSeguindo();
		$this->view->total_seguidores = $usuario->getTotalSeguidores();

		//recuperando seguidores
		$limit_seguidores = 3;
		
		$this->view->seguidores = $usuario->getSeguidores($limit_seguidores);
		$this->view->limite_seguidores = $limit_seguidores;

		$this->render('timeline');
		
	}

	public function timelineSeguidores() {
		$this->validaAutenticacao();
		//recuperação de dados
		$usuario = Container::getModel('usuario');

		$usuario->__set('id', $_SESSION['id']);

		$this->view->info_usuario = $usuario->getInfoUsuario();
		$this->view->total_tweets = $usuario->getTotalTweets();
		$this->view->total_seguindo = $usuario->getTotalSeguindo();
		$this->view->total_seguidores = $usuario->getTotalSeguidores();

		
		//variáveis de paginação
		$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
		$limit_caixa = 3;
		$limit_seguidores = 7;
		$offset = ($pagina - 1) * $limit_seguidores;
		$total_seguidores = $usuario->getTotalSeguidores();
		$total_paginas = ceil($total_seguidores['total_seguidores']/$limit_seguidores);
		$this->view->total_de_paginas = $total_paginas;
		$this->view->pagina_ativa = $pagina;

		//seguidores da caixinha
		$this->view->seguidores = $usuario->getSeguidores($limit_caixa);

		//seguidores da paginação
		$this->view->seguidoresPaginar = $usuario->getSeguidoresPaginar($limit_seguidores, $offset);


		$this->render('timelineSeguidores');

	}

	public function timelineSeguindo() {
		$this->validaAutenticacao();
		//recuperação de dados
		$usuario = Container::getModel('usuario');

		$usuario->__set('id', $_SESSION['id']);

		$this->view->info_usuario = $usuario->getInfoUsuario();
		$this->view->total_tweets = $usuario->getTotalTweets();
		$this->view->total_seguindo = $usuario->getTotalSeguindo();
		$this->view->total_seguidores = $usuario->getTotalSeguidores();

		
		//variáveis de paginação
		$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
		$limit_seguindo = 7;
		$offset = ($pagina - 1) * $limit_seguindo;
		$total_seguindo = $usuario->getTotalSeguindo();
		$total_paginas = ceil($total_seguindo['total_seguindo']/$limit_seguindo);
		$this->view->total_de_paginas = $total_paginas;
		$this->view->pagina_ativa = $pagina;

		//paginação do seguindo
		$this->view->seguindoPaginar = $usuario->getSeguindoPaginar($limit_seguindo, $offset);

		$this->render('timelineSeguindo');

	}

	public function tweet() {

		$this->validaAutenticacao();

		$tweet = Container::getModel('Tweet');

		$tweet->__set('tweet', $_POST['tweet']);
		$tweet->__set('id_usuario', $_SESSION['id']);

		$tweet->salvar();

		header('Location: /timeline');
		
	}

	public function validaAutenticacao() {

		session_start();

		if(!isset($_SESSION['id']) || $_SESSION['id'] == '' || !isset($_SESSION['nome']) || $_SESSION['nome'] == '') {
			header('Location: /?login=erro');
		}	

	}

	public function quemSeguir() {

		$this->validaAutenticacao();

		$pesquisarPor = isset($_GET['pesquisarPor']) ? $_GET['pesquisarPor'] : '';
		
		$usuarios = array();

		if($pesquisarPor != '') {
			
			$usuario = Container::getModel('Usuario');
			$usuario->__set('nome', $pesquisarPor);
			$usuario->__set('id', $_SESSION['id']);
			$usuarios = $usuario->getAll();

		}

		$this->view->usuarios = $usuarios;

		$usuario = Container::getModel('usuario');

		$usuario->__set('id', $_SESSION['id']);

		$this->view->info_usuario = $usuario->getInfoUsuario();
		$this->view->total_tweets = $usuario->getTotalTweets();
		$this->view->total_seguindo = $usuario->getTotalSeguindo();
		$this->view->total_seguidores = $usuario->getTotalSeguidores();

		$sugestoes = Container::getModel('usuario');
		$sugestoes->__set('id', $_SESSION['id']);
		$this->view->sugestoes = $sugestoes->getSugestoes();

		$this->render('quemSeguir');
	}	

	public function acao() {
		$this->validaAutenticacao();

		$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
		$id_usuario_seguindo = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : '';

		$usuario = Container::getModel('Usuario');
		$usuario->__set('id', $_SESSION['id']);

		if($acao == 'seguir') {
			$usuario->seguirUsuario($id_usuario_seguindo);
		} else {
			$usuario->deixarSeguirUsuario($id_usuario_seguindo);
		}

		$pag = $_GET['pag'];

		header('location: /'.$pag);
	}

	public function removerTweet(){
		$this->validaAutenticacao();
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		$tweet = Container::getModel('Tweet');
		$tweet->__set('id',$id);
		$tweet->remover();
		header('location: /timeline');
	}

	public function editarNome() {
		$this->validaAutenticacao();
		$usuario = Container::getModel('usuario');
		$usuario->__set('id', $_SESSION['id']);
		$usuario->__set('nome', $_POST['nome']);
		$validade = $usuario->editarNome();
		if($validade == true) {
			header('location: /timeline?edicao=sucesso');
		}
		if($validade == false) {
			header('location: /timeline?edicao=erro');
		}
	}
}

?>