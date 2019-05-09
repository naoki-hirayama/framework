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