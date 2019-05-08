<?php
 
/**
 * View.
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class View
{
    protected $base_dir;//viewsディレクトリへの絶対パスを指定
    protected $defaults;
    protected $layout_variables = array();

    /**
     * コンストラクタ
     *
     * @param string $base_dir
     * @param array $defaults
     */
    public function __construct($base_dir, $defaults = array())
    {
        $this->base_dir = $base_dir; // "/vagrant/mini-blog/views"
        $this->defaults = $defaults;
    }

    /**
     * レイアウトに渡す変数を指定
     *
     * @param string $name
     * @param mixed $value
     */
    public function setLayoutVar($name, $value)
    {
        $this->layout_variables[$name] = $value;
    }

    /**
     * ビューファイルをレンダリング
     *
     * @param string $_path
     * @param array $_variables
     * @param mixed $_layout
     * @return string
     */
    public function render($_path, $_variables = array(), $_layout = false)
    {   
        /*
        $_variables
        ["user"] => array(4){
        ["id"] => "13" ["user_name"] => "hirayama" ["password"] => "ee946816178c2dbfad3ae0579691d5b109a40bad" ["created_at"] => "2019-05-07 10:06:33"
        }
        ["followings"] => array(0){}

        $_path = "account/index" 

        */

        $_file = $this->base_dir . '/' . $_path . '.php';
        //var_dump($_file);/vagrant/mini-blog/views/account/index.php"
        //var_dump($_file); "/vagrant/mini-blog/views/layout.php"
        //var_dump($this->defaults);

        //var_dump(array_merge($this->defaults, $_variables));

        //p249 
        //var_dump(extract(array_merge($this->defaults, $_variables)));
        extract(array_merge($this->defaults, $_variables));
       
        //バッファという場所にデータをためておく
        ob_start();
        ob_implicit_flush(0);

        require $_file;

        $content = ob_get_clean();

        if ($_layout) {
            $content = $this->render($_layout,
                array_merge($this->layout_variables, array(
                    '_content' => $content,
                )
            ));
        }
        //var_dump($this->base_dir);
        //var_dump($this->defaults);
        return $content;
    }

    /**
     * 指定された値をHTMLエスケープする
     *
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
