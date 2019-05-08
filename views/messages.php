<ul class="message_list">
    <?php foreach ($messages as $message) : ?>
        <li><?php echo $this->escape($message) ?></li>
    <?php endforeach ?>
</ul>