<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if (empty($arResult['ITEMS'])): ?>
    <p>Нет данных о курсах валют</p>
<?php else: ?>
    <div class="local-currencies-rates">
        <table class="local-currencies-table">
            <thead>
            <tr>
                <th>Валюта</th>
                <th>Код</th>
                <th>Курс</th>
                <th>Дата</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($arResult['ITEMS'] as $item): ?>
                <tr>
                    <td><?= htmlspecialcharsbx($item['CURRENCY_NAME']) ?></td>
                    <td><?= htmlspecialcharsbx($item['CURRENCY_CODE']) ?></td>
                    <td><?= number_format($item['RATE'], 4, '.', ' ') ?></td>
                    <td><?= htmlspecialcharsbx($item['RATE_DATE']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif;