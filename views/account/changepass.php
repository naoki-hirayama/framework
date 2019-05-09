<?php $this->setLayoutVar('title', 'パスワード変更') ?>

<h2>パスワード変更</h2>

<?php if (isset($errors) && count($errors) > 0) : ?>
    <?php echo $this->render('errors', array('errors' => $errors)) ?>
<?php endif ?>
<?php if (isset($messages) && count($messages) > 0) : ?>
    <?php echo $this->render('messages', array('messages' => $messages)) ?>
    <a href="<?php echo $base_url ?>/account/detail">OK</a>
<?php endif ?>

<div id="password">
    <form action="<?php echo $base_url ?>/change/password" method="post">

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