<?php

/**
 * @project:   App Icon
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Application\Service\FileManager;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;

/** @var File $appIcon */

$app = Application::getFacadeApplication();
/** @var Form $form */
$form = $app->make(Form::class);
/** @var FileManager $fileManager */
$fileManager = $app->make(FileManager::class);

?>

<div class="ccm-dashboard-header-buttons">
    <a  class="btn btn-secondary" href="javascript:void(0);" id="ccm-report-bug">
        <?php echo t('Get Help') ?>
    </a>
</div>


<script>
    (function ($) {
        $("#ccm-report-bug").click(function () {
            jQuery.fn.dialog.open({
                href: "<?php echo (string)\Concrete\Core\Support\Facade\Url::to("/ccm/system/dialogs/app_icon/create_ticket"); ?>",
                modal: true,
                width: 500,
                title: "<?php echo h(t("Support"));?>",
                height: '80%'
            });
        });
    })(jQuery);
</script>

<div class="row">
    <div class="col-xs-12">
        <form action="#" method="post">
            <fieldset>
                <legend>
                    <?php echo t("Settings"); ?>
                </legend>

                <div class="form-group">
                    <?php echo $form->label("appIcon", t("App Icon")); ?>
                    <?php echo $fileManager->image('appIcon', 'appIcon', t("Select App Icon"), $appIcon); ?>
                </div>
            </fieldset>

            <div class="ccm-dashboard-form-actions-wrapper">
                <div class="ccm-dashboard-form-actions">

                    <div class="float-end">

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save" aria-hidden="true"></i> <?php echo t("Save"); ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>