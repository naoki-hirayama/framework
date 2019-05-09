<?php $this->setLayoutVar('title', '画像編集') ?>

<?php if (isset($errors) && count($errors) > 0) : ?>
    <?php echo $this->render('errors', array('errors' => $errors)) ?>
<?php endif ?>
<?php if (isset($messages) && count($messages) > 0) : ?>
    <?php echo $this->render('messages', array('messages' => $messages)) ?>
    <a href="<?php echo $base_url ?>/account/detail">OK</a>
<?php endif ?>

<form action="<?php echo $base_url; ?>/add/picture" method="post" enctype="multipart/form-data">
    <p>画像：</p>
    <?php if (isset($user['picture'])) : ?>
        <img src="../../../images/images/<?php echo $user['picture'] ?>" width="100" height="100"></br>
    <?php else : ?>
        <p>NO image</p></br>
    <?php endif; ?>
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $picture_max_size ?>">
    <input type="file" name="picture"><br />
    <input type="submit" value="変更する" />
</form>