<?php $this->setLayoutVar('title', 'アカウント') ?>

<h2>アカウント</h2>
<?php if (isset($user['picture'])) : ?>
    <img src="<?php echo $base_img_url ?><?php echo $user['picture'] ?>" width="100" height="100"></br>
<?php else : ?>
    <p>NO image</p></br>
<?php endif; ?>

<a href="<?php echo $base_url ?>/add/picture">画像を編集</a>
<p>
    ユーザID:
    <a href="<?php echo $base_url ?>/user/<?php echo $user['user_name'] ?>">
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
    <li>
        <a href="<?php echo $base_url ?>/change/password">パスワード変更</a>
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