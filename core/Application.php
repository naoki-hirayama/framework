<?php

/**
 * Application.
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
abstract class Application
{
    protected $debug = false;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;

    /**
     * コンストラクタ
     *
     * @param boolean $debug
     */
    public function __construct($debug = false)
    {
        $this->setDebugMode($debug);
        $this->initialize();
        $this->configure();
    }

    /**
     * デバッグモードを設定
     * 
     * @param boolean $debug
     */
    protected function setDebugMode($debug)
    {
        if ($debug) {
            $this->debug = true;
            ini_set('display_errors', 1);
            error_reporting(-1);
        } else {
            $this->debug = false;
            ini_set('display_errors', 0);
        }
    }

    /**
     * アプリケーションの初期化
     */
    protected function initialize()
    {
        $this->request    = new Request();
        $this->response   = new Response();
        $this->session    = new Session();
        $this->db_manager = new DbManager();
        $this->router     = new Router($this->registerRoutes());
        //var_dump($this->registerRoutes()); 
    }

    /**
     * アプリケーションの設定
     */
    protected function configure()
    {
    }

    /**
     * プロジェクトのルートディレクトリを取得
     *
     * @return string ルートディレクトリへのファイルシステム上の絶対パス
     */
    abstract public function getRootDir();  

    /**
     * ルーティングを取得
     *
     * @return array
     */
    abstract protected function registerRoutes();  // '/' => array('controller' => 'status', 'action' => 'index'),

    /**
     * デバッグモードか判定
     *
     * @return boolean
     */
    public function isDebugMode()
    {
        return $this->debug;
    }

    /**
     * Requestオブジェクトを取得
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Responseオブジェクトを取得
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sessionオブジェクトを取得
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * DbManagerオブジェクトを取得
     *
     * @return DbManager
     */
    public function getDbManager()
    {
        return $this->db_manager;
    }

    /**
     * コントローラファイルが格納されているディレクトリへのパスを取得
     *
     * @return string
     */
    public function getControllerDir()
    {
        return $this->getRootDir() . '/controllers';
    }

    /**
     * ビューファイルが格納されているディレクトリへのパスを取得
     *
     * @return string
     */
    public function getViewDir()
    {
        return $this->getRootDir() . '/views';
    }

    /**
     * モデルファイルが格納されているディレクトリへのパスを取得
     *
     * @return stirng
     */
    public function getModelDir()
    {
        return $this->getRootDir() . '/models';
    }

    /**
     * ドキュメントルートへのパスを取得
     *
     * @return string
     */
    public function getWebDir()
    {
        return $this->getRootDir() . '/web';
    }

    /**
     * アプリケーションを実行する
     *
     * @throws HttpNotFoundException ルートが見つからない場合
     */
    public function run()
    {
        
        try {
            //コントローラー名とアクション名を特定
            $params = $this->router->resolve($this->request->getPathInfo());//返り値 "/" 
            //var_dump($params);
            //array(3) { ["controller"]=> string(7) "account" ["action"]=> string(5) "index" [0]=> string(15) "/account/detail" }

            if ($params === false) {
                throw new HttpNotFoundException('No route found for ' . $this->request->getPathInfo());
            }
            //コントローラー、アクション名をそれぞれ代入
            $controller = $params['controller'];
            $action = $params['action'];
            //211行目 
            //$controller = "account"
            //$action = "index"
            //$params = ["controller"]=> "account" ["action"]=> "index" [0]=> "/account/detail"
            $this->runAction($controller, $action, $params);

        } catch (HttpNotFoundException $e) {
            $this->render404Page($e);
        } catch (UnauthorizedActionException $e) {
            list($controller, $action) = $this->login_action;
            $this->runAction($controller, $action);
        }

        $this->response->send();
        
    }

    /**
     * 指定されたアクションを実行する
     *
     * @param string $controller_name
     * @param string $action
     * @param array $params
     *
     * @throws HttpNotFoundException コントローラが特定できない場合
     */

    //$controller_name = "account"
    //$action = "index"
    //$params = ["controller"]=> "account" ["action"]=> "index" [0]=> "/account/detail"
    public function runAction($controller_name, $action, $params = array())
    {
        //ucfirst — 文字列の最初の文字を大文字にする

        //$controller_class = "StatusController";
        $controller_class = ucfirst($controller_name) . 'Controller';

        //インスタンスを代入
        $controller = $this->findController($controller_class);

        if ($controller === false) {
            throw new HttpNotFoundException($controller_class . ' controller is not found.');
        }

        //controller->run メソッド　24行目　$action = $params['action']; actionを実行
        $content = $controller->run($action, $params);

        //var_dump($content);
        $this->response->setContent($content);
    }

    /**
     * 指定されたコントローラ名から対応するControllerオブジェクトを取得
     *
     * @param string $controller_class
     * @return Controller
     */
    protected function findController($controller_class)
    {   
        //$controller_class = "StatusController";
        //クラスが定義済みかどうかを確認する
        if (!class_exists($controller_class)) {
            //ファイルのパスを作る
            $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';
            //ファイルが存在し、読み込み可能であるかどうかを調べる
            if (!is_readable($controller_file)) {
                return false;
            } else {
                require_once $controller_file;

                if (!class_exists($controller_class)) {
                    return false;
                }
            }
        }
        
        return new $controller_class($this);
        //例 $this: AccountController
    }

    /**
     * 404エラー画面を返す設定
     *
     * @param Exception $e
     */
    protected function render404Page($e)
    {
        $this->response->setStatusCode(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $this->response->setContent(<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>404</title>
</head>
<body>
    {$message}
</body>
</html>
EOF
        );
    }
}
