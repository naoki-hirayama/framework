<?php

/**
 * Router.
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class Router
{
    protected $routes;

    /**
     * コンストラクタ
     *
     * @param array $definitions
     */
    public function __construct($definitions)
    {
        $this->routes = $this->compileRoutes($definitions);
        //var_dump($this->routes);
        
    }

    /**
     * ルーティング定義配列を内部用に変換する
     *
     * @param array $definitions
     * @return array
     */
    public function compileRoutes($definitions)
    {
        
        //ルーティング定義 から $this->routes を作成する
        $routes = array();

        foreach ($definitions as $url => $params) {
            $tokens = explode('/', ltrim($url, '/'));
            foreach ($tokens as $i => $token) {
                if (0 === strpos($token, ':')) {
                    $name = substr($token, 1);
                    $token = '(?P<' . $name . '>[^/]+)';
                }
                $tokens[$i] = $token;
            }

            $pattern = '/' . implode('/', $tokens);
            $routes[$pattern] = $params;
        }
        
        return $routes;
    }

    /**
     * 指定されたPATH_INFOを元にルーティングパラメータを特定する
     *
     * @param string $path_info
     * @return array|false
     */
    public function resolve($path_info) //引数 "/" p219
    {
        //$this->routes を検索して PATH_INFOから controller, action を特定する

        if ('/' !== substr($path_info, 0, 1)) {
            $path_info = '/' . $path_info;//$path_infoの先頭が"/" でない時
        }

        foreach ($this->routes as $pattern => $params) {
            if (preg_match('#^' . $pattern . '$#', $path_info, $matches)) {
                $params = array_merge($params, $matches); 

                //var_dump($params);
                //["controller"]=> "status" ["action"]=> "index" [0]=> "/" 
                return $params;
            }
        }

        return false;
    }
}
