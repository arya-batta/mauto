<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$stageaccess=$security->isGranted('stage:stages:view');
$isAdmin    =$view['security']->isAdmin();
?>
<?php foreach ($items as $item): ?>
    <?php /** @var \Mautic\LeadBundle\Entity\Lead $item */ ?>
    <?php $fields = $item->getFields(); ?>
    <tr<?php if (!empty($highlight)): echo ' class="warning"'; endif; ?>>
        <td class="table-description">
            <span>
                <?php if (in_array($item->getId(), array_keys($noContactList)))  : ?>
                    <div class="pull-right label label-danger"><i class="fa fa-ban"> </i></div>
                <?php endif; ?>
                <div> <?php echo ($item->isAnonymous()) ? $view['translator']->trans($item->getPrimaryIdentifier()) : $item->getPrimaryIdentifier(); ?></div>
                <div class="small"><?php echo $item->getSecondaryIdentifier(); ?></div>
            </span>
        </td>
        <td class="">
            <span>
                <?php echo $fields['core']['email']['value']; ?>
            </span>
        </td>
        <td class="text-center">
           <?php
            $score = (!empty($fields['core']['score']['value'])) ? $view['assets']->getLeadScoreIcon($fields['core']['score']['value']) : '';
           ?>
           <img src="<?php echo $score; ?>" style="max-height: 25px;" />

        </td>
        <td class="text-center">
            <?php
            $color = $item->getColor();
            $style = !empty($color) ? ' style="background-color: '.$color.';"' : '';
            ?>
            <span class="label label-primary"><?php echo $item->getPoints(); ?></span>
        </td>
        <td class="">
            <abbr title="<?php echo $view['date']->toFull($item->getLastActive()); ?>">
                <?php echo $view['date']->toText($item->getLastActive()); ?>
            </abbr>
        </td>
        <td class="">
            <?php
            $flag = (!empty($fields['core']['country'])) ? $view['assets']->getCountryFlag($fields['core']['country']['value']) : '';
            if (!empty($flag)):
                ?>
                <img src="<?php echo $flag; ?>" style="max-height: 24px;" class="mr-sm" />
            <?php
            endif;
            $location = [];
            if (!empty($fields['core']['city']['value'])):
                $location[] = $fields['core']['city']['value'];
            endif;
            if (!empty($fields['core']['state']['value'])):
                $location[] = $fields['core']['state']['value'];
            elseif (!empty($fields['core']['country']['value'])):
                $location[] = $fields['core']['country']['value'];
            endif;
            echo implode(', ', $location);
            ?>
            <div class="clearfix"></div>
        </td>
    </tr>
<?php endforeach; ?>