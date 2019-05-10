<?php

/**
 * Request.
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class Request
{
    /**
     * リクエストメソッドがPOSTかどうか判定
     *
     * @return boolean
     */
    public function isPost()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return true;
        }

        return false;
    }

    /**
     * GETパラメータを取得
     *
     * @param string $name
     * @param mixed $default 指定したキーが存在しない場合のデフォルト値
     * @return mixed
     */
    public function getGet($name, $default = null)
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }

        return $default;
    }

    /**
     * POSTパラメータを取得
     *
     * @param string $name
     * @param mixed $default 指定したキーが存在しない場合のデフォルト値
     * @return mixed
     */
    public function getPost($name, $default = null)
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }

        return $default;
    }
    //追加
    public function getFiles($name, $default = null)
    {
        if (isset($_FILES[$name])) {
            return $_FILES[$name];
        }

        return $default;
    }

    /**
     * ホスト名を取得
     *
     * @return string
     */
    public function getHost()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * SSLでアクセスされたかどうか判定
     *
     * @return boolean
     */
    public function isSsl()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }
        return false;
    }

    /**
     * リクエストURIを取得
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $_SERVER['REQUEST_URI'];//getパラメーターまで
    }

    /**
     * ベースURLを取得
     *
     * @return string
     */
    public function getBaseUrl()//ベースURLを取得する
    {   
        $script_name = $_SERVER['SCRIPT_NAME'];//フロントコントローラーまで
        // /mini-blog/web/index_dev.php

        $request_uri = $this->getRequestUri();//getパラメーターまで
        // /mini-blog/web/index_dev.php/account/detail

        if (0 === strpos($request_uri, $script_name)) {
            return $script_name;//fcがURLに含まれる場合
        } else if (0 === strpos($request_uri, dirname($script_name))) {
            return rtrim(dirname($script_name), '/');//fcが省略されている場合
        }

        return '';
    }
    //追加
    public function getBaseImgUrl()
    {
        $base_url = $this->getBaseUrl();
        $pattern = '/web.+$/';
        $replacement = 'images/';
        return preg_replace($pattern, $replacement, $base_url);
    }

    /**
     * PATH_INFOを取得
     *
     * @return string
     */
    public function getPathInfo()//PATH_INFOを取得する
    {
        $base_url = $this->getBaseUrl();

        $request_uri = $this->getRequestUri();

        if (false !== ($pos = strpos($request_uri, '?'))) {
            $request_uri = substr($request_uri, 0, $pos);
        }

        $path_info = (string)substr($request_uri, strlen($base_url));
        //var_dump($path_info); "/" 
        return $path_info;
    }
}
