<?php
declare (strict_types = 1);

namespace Cyndaron\Mailform;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'mailform';
    const TABLE = 'mailformulieren';
    const SAVE_URL = '/editor/mailform/%s';

    /** @var Mailform|null  */
    protected $model = null;

    protected function prepare()
    {
        if ($this->id)
        {
            $this->model = new Mailform((int)$this->id);
            $this->model->load();
            $this->content = $this->model->confirmationText;
            $this->contentTitle = $this->model->name;
        }
    }

    protected function showContentSpecificButtons()
    {
        $checked = boolval($this->model->sendConfirmation ?? false);
        echo $this->showCheckbox('sendConfirmation', 'Stuur bovenstaande tekst als bevestiging', $checked);
        ?>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="email">E-mailadres: </label>
            <div class="col-sm-5">
                <input type="email" class="form-control" id="email" name="email" value="<?=$this->model->email ?? '';?>" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="antiSpamAnswer">Antispamantwoord: </label>
            <div class="col-sm-5">
                <input type="text" class="form-control" id="antiSpamAnswer" name="antiSpamAnswer" value="<?=$this->model->antiSpamAnswer ?? '';?>" />
            </div>
        </div>
        <?php
    }
}