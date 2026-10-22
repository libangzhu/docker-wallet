<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-15
 * Time: 下午2:10
 */
/**
 * @var array $exchanges
 */
?>
<?php foreach ($exchanges as $exchange): ?>
    <li><a href="<?= $exchange['url'] ?>"><?= $exchange['name'] ?></a></li>
<?php endforeach; ?>
