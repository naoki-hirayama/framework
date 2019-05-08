<?php $this->setLayoutVar('title', 'アカウント') ?>

<h2>アカウント</h2>
<p>
    ユーザID:
    <a href="<?php echo $base_url ?>/user/<?php echo $this->escape($user['user_
    name']); ?>">
        <strong><?php echo $this->escape($user['user_name']); ?></strong>
    </a>
</p>

<ul>
    <li>
        <a href="<?php echo $base_url; ?>/">ホーム</a>
    </li>
    <li>
        <a href="<?php echo $base_url; ?>/account/signout">ログアウト</a>
    </li>
</ul>

<h3>フォロー中</h3>

<?php if (count($followings) > 0) : ?>
    <ul>
        <?php foreach ($followings as $following) : ?>
            <li>
                <a href="<?php echo $base_url; ?>/user/<?php echo $this->escape($following['user_name']); ?>">
                    <?php echo $this->escape($following['user_name']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (isset($errors) && count($errors) > 0) : ?>
    <?php echo $this->render('errors', array('errors' => $errors)) ?>
<?php endif ?>
<?php if (isset($messages) && count($messages) > 0) : ?>
    <?php echo $this->render('messages', array('messages' => $messages)) ?>
    <a href="<?php echo $base_url ?>/account/detail">OK</a>
<?php endif ?>

<div id="password">
    <h3>パスワード変更</h3>
    <form action="<?php echo $base_url ?>/account/detail" method="post">

        <input type="hidden" name="_token" value="<?php echo $this->escape($_token) ?>">
        <table>
            <tbody>
                <tr>
                    <th>現在のパスワード </th>
                    <td>
                        <input type="password" name="current_password" value="">
                    </td>
                </tr>
                <tr>
                    <th>新しいパスワード</th>
                    <td>
                        <input type="password" name="new_password" value="">
                    </td>
                </tr>
                <tr>
                    <th>確認用パスワード</th>
                    <td>
                        <input type="password" name="confirm_password" value="">
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <input type="submit" value="変更する">
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>