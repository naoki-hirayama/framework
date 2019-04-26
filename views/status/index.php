<?php $this->setLayoutVar('title', 'ホーム') ?>

<h2>ホーム</h2>

<form action="<?php echo $base_url; ?>/status/post" method="post">
    <input type="hidden" name="_token" value="<?php echo $this->escape($_token); ?>" />

    <?php if (isset($errors) && count($errors) > 0) : ?>
        <?php echo $this->render('errors', array('errors' => $errors)) ?>
    <?php endif; ?>

    <textarea name="body" rows="2" cols="60"><?php echo $this->escape($body); ?></textarea>
    <p>
        <input type="submit" value="発言" />
    </p>
</form>

<div id="statuses">
    <?php foreach ($statuses as $status) : ?>
        <?php echo $this->render('status/status', array('status' => $status)); ?>
    <?php endforeach; ?>
</div>

<!--ページング処理-->
<div id="pager">
    <?php if ($pager->hasPreviousPage()) : ?>
        <a href="<?php echo $pager->getPreviousPage() ?>">前へ</a>
    <?php endif ?>

    <?php foreach ($pager->getPageNumbers() as $i) : ?>
        <?php if ($i === $pager->getCurrentPage()) : ?>
            <span>
                <?php echo $i ?>
            </span>
        <?php else : ?>
            <a href="<?php echo $i ?>">
                <?php echo $i ?>
            </a>
        <?php endif ?>
    <?php endforeach ?>

    <?php if ($pager->hasNextPage()) : ?>
        <a href="<?php echo $pager->getNextPage() ?>">次へ</a>
    <?php endif ?>
</div>
<!--ここまで-->