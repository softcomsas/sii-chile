<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $items array */

$companyName = "Sistema Ayala";
$logoUrl = "http://sistema.ayalarepuestos.cl/favicon.ico";

$title = $title ?? "";
$subtitle = $subtitle ?? "";
$footerText = $footerText ?? "";

$columns = $columns ?? [];
$items = $items ?? [];
$type = $type ?? 'error';

// Definir colores según el tipo
$colors = [
    'info' => [
        'background' => '#f0f8ff', // Azul claro para informativo
        'font' => '#007acc',
        'titleRowBackground' => '#007acc',
    ],
    'warning' => [
        'background' => '#fff3cd', // Amarillo claro para advertencia
        'font' => '#e25822',
        'titleRowBackground' => '#e25822',
    ],
    'error' => [
        'background' => '#ffe6e6', // Rojo claro para error (valor por defecto)
        'font' => '#e25822',
        'titleRowBackground' => '#e25822',
    ],
];

$backgroundColor = $colors[$type]['background'];
$fontColor = $colors[$type]['font'];
$titleRowBackgroundColor = $colors[$type]['titleRowBackground'];

/*$backgroundColor = '#fff3cd';
$fontColor = '#e25822';
$titleRowBackgroundColor = '#e25822';*/

?>

<div class="low-stock-mail" style="background-color: <?= $backgroundColor ?>; padding: 10px;">
    <p style="font-size: 24px; font-weight: bold; color: <?= $fontColor ?>;"><?= $title ?></p>

    <?php if ($subtitle) : ?><p style="font-size: 16px; font-weight: bold; color: <?= $fontColor ?>;"><?= $subtitle ?></p><?php endif; ?>

    <?php if ($columns) : ?>
        <table style="border-collapse: collapse; width: 100%;">
            <tr>
                <?php foreach ($columns as $column => $label) : ?>
                    <th style="border: 1px solid black; padding: 5px; background-color: <?= $titleRowBackgroundColor ?>; color: #fff; text-align: center;"><?= $label ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($items as $item) : ?>
                <tr>
                    <?php foreach ($columns as $column => $label) : ?>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;"><?= Html::encode($item[$column]) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if ($footerText) : ?><p style="font-size: 16px; font-weight: bold; color: <?= $fontColor ?>;"><?= $footerText ?></p><?php endif; ?>

    <p style="font-size: 16px; font-weight: bold; color: <?= $fontColor ?>;">Gracias por tu atención.</p>

    <!-- Firma de la empresa (logo y nombre) 
    <div style="text-align: left; margin-top: 20px;">
        <img src="<?= $logoUrl ?>" alt="<?= $companyName ?>" style="max-width: 100px;">
        <p style="font-size: 16px; color: <?= $fontColor ?>; margin-top: 5px;"><?= $companyName ?></p>
    </div>-->
</div>