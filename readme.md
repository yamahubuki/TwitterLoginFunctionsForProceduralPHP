# TwitterLogin Functions for procedural

## 手続き型でtwitter連携ログインを利用するためのモジュール


### ver.2019-03-30

### 必要環境
- PHP7以上
- Abraham氏作成のTwitterOAuthモジュールを格納したディレクトリが、このファイルのあるディレクトリにあること。

### 制約など
- Twitterの多くの機能の内、Twitter連携ログインに必要な機能のみをまとめています。

### 利用方法
1. このファイルをrequireする
1. twitter_getRequestTokenを呼び出す
	- 引数
		- Twitterから発行されたコンシューマーキー(string)
		- Twitterから発行されたコンシューマーシークレット(string)
		- リダイレクト先URL(省略可)(String)
	- 戻り値
		- リクエストトークンを含む連想配列(成功)またはnull(エラー)
		- 連想配列には["oauth_token"]と["oauth_token_secret"]が含まれる。  
		  これは一度限り使えるOauthリクエストのためのトークンである。
1. 取得したoauth_tokenとoauth_token_secretのペアをどこかに保存する。  
　 セッションを使うのが一番簡単。
1. twitter_getURL()を呼び出し、リダイレクト先のURLを取得し、取得したURLにユーザをリダイレクトする
	- 引数
		- method_type(bool)
			- true  : oauth/authorize		毎回認証画面を表示
			- false : oauth/authenticate	認証済みの場合は認証画面をスキップ
		- request_token(String)
1. ユーザがTwitterのページで認証操作を行う
1. リダイレクト先にて、このファイルをrequireする。
1. twitter_getAccessTokenを呼び出す
	- 引数
		- Twitterから発行されたコンシューマーキー(string)
		- Twitterから発行されたコンシューマーシークレット(string)
		- request_token(String)
		- request_token_secret(String)
	- 戻り値
		- アクセストークンを含む連想配列(成功)またはnull(エラー)
		- 連想配列には["oauth_token"]と["oauth_token_secret"]が含まれる。  
		  これはユーザが認証を取り消さない限り永久に使えるトークンである。
1. twitter_getUserInfoを呼び出す
	- 引数
		- Twitterから発行されたコンシューマーキー(string)
		- Twitterから発行されたコンシューマーシークレット(string)
		- access_token(String)
		- access_token_secret(String)
		- need_email_address(bool)  
		  ※trueにする場合は事前にtwitterの開発者サイト上でemailの取得権限を得ておくこと！
	- 戻り値
		- ユーザ情報を含む配列またはnull(エラー)
9. それぞれの関数呼出しにてnullが返された直後には、twitter_getErrorString()でエラー内容を取得できる。



