<?php

/**
 * AccountController.
 *
 * @author Katsuhiro Ogawa <fivestar@nequal.jp>
 */
class AccountController extends Controller
{
    protected $auth_actions = array('index', 'signout', 'follow', 'addPic');

    //画像編集メソッド
    public function addPicAction()
    {
        $user = $this->session->get('user');
        $picture_max_size = 1*1024*1024;
        $messages = [];
        $errors = []; 
        var_dump($this->application->getRootDir());
        var_dump($this->request->getBaseImgUrl());
        //post送信された時
        if ($this->request->isPost()) {
            
            $picture = $this->request->getFiles('picture');//Request.php 56行目
            
            if (strlen($picture['name']) === 0) {
                $errors[] = "画像を選択してください";
            } else {
                if ($picture['error'] === UPLOAD_ERR_FORM_SIZE) {
                    $errors[] = "サイズが" . number_format($picture_max_size) . "MBを超えています。";
                } else if ($picture['size'] > $picture_max_size) {
                    $errors[] = "不正な操作です。";
                } else {
                    // 画像ファイルのMIMEタイプチェック
                    $posted_picture = $picture['tmp_name'];
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $picture_type = $finfo->file($posted_picture);

                    $vaild_picture_types = [
                        'image/png',
                        'image/gif',
                        'image/jpeg'
                    ];

                    if (!in_array($picture_type, $vaild_picture_types)) {
                        $errors[] = "画像が不正です。";
                    }
                }
            }
            
            if (count($errors) === 0) {
                
                $posted_picture = $picture['tmp_name'];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $picture_type = $finfo->file($posted_picture);
                $specific_num = uniqid(mt_rand());
                $rename_file = $specific_num . '.' . basename($picture_type);
                $rootdir = $this->application->getRootDir();
                $rename_file_path = $rootdir . '/images/' . $rename_file;
                
                move_uploaded_file($picture['tmp_name'], $rename_file_path);

                if (empty($user['picture'])) {
                    $messages[] = "画像を設定しました";

                } else {
                    unlink("{$rootdir}/images/{$user['picture']}");
                    $messages[] = "新しい画像に変更しました";
                }
                
                $user_repository = $this->db_manager->get('User');
                $sql = 'UPDATE user SET picture = :picture WHERE user_name = :user_name';

                $stmt = $user_repository->execute($sql, array(
                    ':user_name' => $user['user_name'],
                    ':picture' => $rename_file,
                ));

                $user = $user_repository->fetchByUserName($user['user_name']);
                $this->session->set('user', $user);
            }
        }
        
        return $this->render(array(
            'user'             => $user,
            'messages'         => $messages,
            'errors'           => $errors,
            'picture_max_size' => $picture_max_size,
        ), 'addpic');
    }

    public function signupAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/account');
        }

        return $this->render(array(
            'user_name' => '',
            'password'  => '',
            '_token'    => $this->generateCsrfToken('account/signup'),
        ));
    }

    public function registerAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/account');
        }

        if (!$this->request->isPost()) {
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('account/signup', $token)) {
            return $this->redirect('/account/signup');
        }

        $user_name = $this->request->getPost('user_name');
        $password = $this->request->getPost('password');

        $errors = array();

        if (!strlen($user_name)) {
            $errors[] = 'ユーザIDを入力してください';
        } else if (!preg_match('/^\w{3,20}$/', $user_name)) {
            $errors[] = 'ユーザIDは半角英数字およびアンダースコアを3 ～ 20 文字以内で入力してください';
        } else if (!$this->db_manager->get('User')->isUniqueUserName($user_name)) {
            $errors[] = 'ユーザIDは既に使用されています';
        }

        if (!strlen($password)) {
            $errors[] = 'パスワードを入力してください';
        } else if (4 > strlen($password) || strlen($password) > 30) {
            $errors[] = 'パスワードは4 ～ 30 文字以内で入力してください';
        }

        if (count($errors) === 0) {
            $this->db_manager->get('User')->insert($user_name, $password);
            $this->session->setAuthenticated(true);

            $user = $this->db_manager->get('User')->fetchByUserName($user_name);
            $this->session->set('user', $user);

            return $this->redirect('/');
        }

        return $this->render(array(
            'user_name' => $user_name,
            'password'  => $password,
            'errors'    => $errors,
            '_token'    => $this->generateCsrfToken('account/signup'),
        ), 'signup');
    }

    public function indexAction()
    {
        $user = $this->session->get('user');
        $followings = $this->db_manager->get('User')
            ->fetchAllFollowingsByUserId($user['id']);

        return $this->render(array(
            'user'       => $user,
            'followings' => $followings,
        ));
    }

    public function changePasswordAction()
    {
        $user = $this->session->get('user');

        $messages = [];
        $errors = [];
        if ($this->request->isPost()) {
            //post送信された時
            $user_repository = $this->db_manager->get('User');
            $current_password = $this->request->getPost('current_password');
            $new_password = $this->request->getPost('new_password');
            $confirm_password = $this->request->getPost('confirm_password');
            $token = $this->request->getPost('_token');

            if (!$this->checkCsrfToken('account/signin', $token)) {
                return $this->redirect('/');
            }

            if ($user['password'] !== $user_repository->hashPassword($current_password)) {
                $errors[] = "パスワードが間違っています。";
            } else {
                if ($new_password !== $confirm_password) {
                    $errors[] = '確認パスワードが一致しません';
                } elseif (strlen($new_password) < 4 || strlen($new_password) > 30) {
                    $errors[] = 'パスワード は４〜30字以内で入力してください';
                }
            }

            if (count($errors) === 0) {
                $password = $user_repository->hashPassword($new_password);
                $sql = 'UPDATE user SET password = :password WHERE user_name = :user_name';

                $stmt = $user_repository->execute($sql, array(
                    ':user_name' => $user['user_name'],
                    ':password' => $password,
                ));

                $user = $user_repository->fetchByUserName($user['user_name']);
                $this->session->set('user', $user);

                $messages[] = "パスワードを変更しました";
            }
        }

        return $this->render(array(
            'user'       => $user,
            'messages'   => $messages,
            'errors'     => $errors,
            '_token'     => $this->generateCsrfToken('account/signin'),
        ), 'changepass');
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

    public function authenticateAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/account');
        }

        if (!$this->request->isPost()) {
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('account/signin', $token)) {
            return $this->redirect('/account/signin');
        }

        $user_name = $this->request->getPost('user_name');
        $password = $this->request->getPost('password');

        $errors = array();

        if (!strlen($user_name)) {
            $errors[] = 'ユーザIDを入力してください';
        }

        if (!strlen($password)) {
            $errors[] = 'パスワードを入力してください';
        }

        if (count($errors) === 0) {
            $user_repository = $this->db_manager->get('User');
            $user = $user_repository->fetchByUserName($user_name);

            if (!$user
                || ($user['password'] !== $user_repository->hashPassword($password))
            ) {
                $errors[] = 'ユーザIDかパスワードが不正です';
            } else {
                $this->session->setAuthenticated(true);
                $this->session->set('user', $user);

                return $this->redirect('/');
            }
        }

        return $this->render(array(
            'user_name' => $user_name,
            'password'  => $password,
            'errors'    => $errors,
            '_token'    => $this->generateCsrfToken('account/signin'),
        ), 'signin');
    }

    public function signoutAction()
    {
        $this->session->clear();
        $this->session->setAuthenticated(false);

        return $this->redirect('/account/signin');
    }

    public function followAction()
    {
        if (!$this->request->isPost()) {
            $this->forward404();
        }

        $following_name = $this->request->getPost('following_name');
        if (!$following_name) {
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('account/follow', $token)) {
            return $this->redirect('/user/' . $following_name);
        }

        $follow_user = $this->db_manager->get('User')
            ->fetchByUserName($following_name);
        if (!$follow_user) {
            $this->forward404();
        }

        $user = $this->session->get('user');

        $following_repository = $this->db_manager->get('Following');
        if ($user['id'] !== $follow_user['id'] 
            && !$following_repository->isFollowing($user['id'], $follow_user['id'])
        ) {
            $following_repository->insert($user['id'], $follow_user['id']);
        }

        return $this->redirect('/account/detail');
    }
}
