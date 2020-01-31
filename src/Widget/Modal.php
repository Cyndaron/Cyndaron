<?php
declare (strict_types = 1);

namespace Cyndaron\Widget;

class Modal extends Widget
{
    public function __construct(string $id, string $title, $body, $footer, string $sizeClass = '')
    {
        ob_start();
        ?>
        <div id="<?=$id?>" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog <?=$sizeClass?>" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?=$title?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Sluiten">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?=$body?>
                    </div>
                    <div class="modal-footer">
                        <?=$footer?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $this->code = ob_get_clean();
    }
}