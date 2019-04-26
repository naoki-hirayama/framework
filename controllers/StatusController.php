<?php

/**
 * StatusController.
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class StatusController extends Controller
{
    protected $auth_actions = array('index', 'post');

    public function indexAction($params)
    {
        $user = $this->session->get('user');
        //総投稿数を取得
        $sql = 'SELECT COUNT(*) cnt FROM status a LEFT JOIN user u ON a.user_id = u.id LEFT JOIN following f ON f.following_id = a.user_id AND f.user_id = :user_id WHERE f.user_id = :user_id OR u.id = :user_id';
        $total_records = $this->db_manager->get('Status')->fetch($sql, array(
            ':user_id' =>$user['id']
        ));

        $max_pager_range = 4;
        $per_page_records = 5;

        if (isset($params['page'])) {
            $page = $params['page'];
        } else {
            $page = 1;
        }
        
        $pager = new Pager($total_records['cnt'], $max_pager_range, $per_page_records);
        $pager->setCurrentPage($page);
        $offset = $pager->getOffset();
        $per_page_records = $pager->getPerPageRecords();
        $e[] = $offset;
        var_dump($e);
        $sql = "SELECT a.*,u.user_name FROM status a LEFT JOIN user u ON a.user_id = u.id LEFT JOIN following f ON f.following_id = a.user_id AND f.user_id = :user_id WHERE f.user_id = :user_id OR u.id = :user_id ORDER BY a.created_at DESC LIMIT {$offset},{$per_page_records}";
        $statuses = $this->db_manager->get('Status')->fetchAll($sql, array(
            ':user_id' => $user['id']
        ));
        
        return $this->render(array(
            'statuses' => $statuses,
            'body'     => '',
            '_token'   => $this->generateCsrfToken('status/post'),
            'pager'    => $pager,
        ));
    }

    public function postAction()
    {
        if (!$this->request->isPost()) {
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('status/post', $token)) {
            return $this->redirect('/');
        }

        $body = $this->request->getPost('body');

        $errors = array();

        if (!strlen($body)) {
            $errors[] = 'ひとことを入力してください';
        } else if (mb_strlen($body) > 200) {
            $errors[] = 'ひとことは200 文字以内で入力してください';
        }

        if (count($errors) === 0) {
            $user = $this->session->get('user');
            $this->db_manager->get('Status')->insert($user['id'], $body);

            return $this->redirect('/');
        }

        $user = $this->session->get('user');
        $statuses = $this->db_manager->get('Status')
            ->fetchAllPersonalArchivesByUserId($user['id']);

        return $this->render(array(
            'errors'   => $errors,
            'body'     => $body,
            'statuses' => $statuses,
            '_token'   => $this->generateCsrfToken('status/post'),
        ), 'index');
    }

    public function userAction($params)
    {
        $user = $this->db_manager->get('User')
            ->fetchByUserName($params['user_name']);
        if (!$user) {
            $this->forward404();
        }

        $statuses = $this->db_manager->get('Status')
            ->fetchAllByUserId($user['id']);
        
        $following = null;
        if ($this->session->isAuthenticated()) {
            $my = $this->session->get('user');
            if ($my['id'] !== $user['id']) {
                $following = $this->db_manager->get('Following')
                    ->isFollowing($my['id'], $user['id']);
            }
        }

        return $this->render(array(
            'user'      => $user,
            'statuses'  => $statuses,
            'following' => $following,
            '_token'    => $this->generateCsrfToken('account/follow'),
        ));
    }

    public function showAction($params)
    {
        $status = $this->db_manager->get('Status')
            ->fetchByIdAndUserName($params['id'], $params['user_name']);

        if (!$status) {
            $this->forward404();
        }

        return $this->render(array('status' => $status));
    }

    public function signinAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/account');
        }

        return $this->render(array(
            'user_name' => '',
            'password'  => '',
            '_token'    => $this->generateCsrfToken('account/signin'),
        ));
    }
}
