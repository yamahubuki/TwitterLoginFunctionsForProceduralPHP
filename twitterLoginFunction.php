<?php

/*******************************************************************
		TwitterLogin Functions for procedural

		手続き型でtwitter連携ログインを利用するためのモジュール


		ver.2019-03-30
		(c)Fubuki


		■必要環境
		・PHP7以上
		・Abraham氏作成のTwitterOAuthモジュールを格納したディレクトリが、
		　このファイルのあるディレクトリにあること。

		■制約など
		・Twitterの多くの機能の内、Twitter連携ログインに必要な機能のみをまとめています。

		■利用方法
		1.このファイルをrequireする
		2.twitter_getRequestTokenを呼び出す
			引数：		string Twitterから発行されたコンシューマーキー
						string Twitterから発行されたコンシューマーシークレット
						String リダイレクト先URL(省略可)
			戻り値：	リクエストトークンを含む連想配列(成功)またはnull(エラー)
						連想配列には["oauth_token"]と["oauth_token_secret"]が含まれる。
						これは一度限り使えるOauthリクエストのためのトークンである。
		3.取得したoauth_tokenとoauth_token_secretのペアをどこかに保存する。
		　セッションを使うのが一番簡単。
		4.twitter_getURL()を呼び出し、リダイレクト先のURLを取得し、取得したURLにユーザをリダイレクトする
			引数：		bool  method_type
								true :oauth/authorize		毎回認証画面を表示
								false：oauth/authenticate	認証済みの場合は認証画面をスキップ
						String request_token
		5.ユーザがTwitterのページで認証操作を行う
		6.リダイレクト先にて、このファイルをrequireする。
		7.twitter_getAccessTokenを呼び出す
			引数：		string Twitterから発行されたコンシューマーキー
						string Twitterから発行されたコンシューマーシークレット
						String request_token
						String request_token_secret
			戻り値：	リクエストトークンを含む連想配列(成功)またはnull(エラー)
						連想配列には["oauth_token"]と["oauth_token_secret"]が含まれる。
						これはユーザが認証を取り消さない限り永久に使えるトークンである。
		8.twitter_getUserInfoを呼び出す
			引数：		string Twitterから発行されたコンシューマーキー
						string Twitterから発行されたコンシューマーシークレット
						String access_token
						String access_token_secret
						bool   need_email_address
						       ※trueにする場合は事前にtwitterの開発者サイト上でemailの取得権限を得ておくこと！
			戻り値：	ユーザ情報を含む配列またはnull(エラー)
		9.それぞれの関数呼出しにてnullが返された直後には、twitter_getErrorString()でエラー内容を取得できる。

*******************************************************************/

require_once("twitteroauth/autoload.php");
use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;

	$twitter_error=null;		//エラー発生時にはここに例外オブジェクトを入れておく

	function twitter_start($key,$secret,$token=null,$tokenSecret=null){
		$connection=new twitterOAuth($key,$secret,$token,$tokenSecret);
		return $connection;
	}

	function twitter_getRequestToken($key,$secret,$URL=null){
		global $twitter_error;
		$twitter_error=null;
		$connection=twitter_start($key,$secret);
		try {
			if (!empty($URL)){
				$request_token=$connection->oauth("oauth/request_token",array("oauth_callback" =>$URL));
			} else {
				$request_token=$connection->oauth("oauth/request_token",array());
			}
		} catch (TwitterOAuthException $e) {
			$twitter_error=$e;
			return null;
		}
		return $request_token;
	}

	function twitter_getURL(bool $methodType,$token){
		$twitter_error=null;
		$connection=twitter_start("","");
		if ($methodType==true){
			$method="oauth/authorize";
		} else {
			$method="oauth/authenticate";
		}
		return $connection->url($method,array("oauth_token"=>$token));
	}

	function twitter_getAccessToken($key,$secret,$token,$tokenSecret){
		global $twitter_error;
		$twitter_error=null;

		//必用なパラメータを受け取っているか確認
		//Twitterから返されたOAuthトークンと、この関数のパラメータで指定されたものとの一致を確認
		if (empty($_REQUEST['oauth_token']) || empty($_REQUEST['oauth_verifier']) || $token !== $_REQUEST['oauth_token']) {
			if (isset($_REQUEST["denied"])){
				$twitter_error=new Exception("authorization canceled by user.");
			} else {
				$twitter_error=new Exception("required parameter not found.");
			}
			return null;
		}

		$connection=twitter_start($key,$secret,$token,$tokenSecret);
		try {
			return $connection->oauth("oauth/access_token",array("oauth_verifier"=>$_REQUEST['oauth_verifier']));
		} catch (TwitterOAuthException $e) {
			$twitter_error=$e;
			return null;
		}
	}

	function twitter_getUserInfo($key,$secret,$token,$tokenSecret,bool $getEmail=false){
		global $twitter_error;
		$twitter_error=null;
		$connection=twitter_start($key,$secret,$token,$tokenSecret);
		try {
			return $connection->get("account/verify_credentials",array("include_email"=>$getEmail));
		} catch (TwitterOAuthException $e) {
			$twitter_error=$e;
			return null;
		}
	}

	function twitter_getErrorString(){
		global $twitter_error;
		if ($twitter_error==null){
			return "";
		}
		return $twitter_error->getMessage();
	}

?>