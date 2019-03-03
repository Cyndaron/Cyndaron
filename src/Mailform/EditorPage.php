<?php
declare (strict_types = 1);

namespace Cyndaron\Mailform;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    protected $type = 'mailform';
    protected $table = 'mailformulieren';
    protected $saveUrl = '/editor/mailform/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->form = Mailform::loadFromDatabase((int)$this->id)->asArray();
            $this->content = $this->form['confirmation_text'];
            $this->contentTitle = $this->form['naam'];
        }
    }

    protected function showContentSpecificButtons()
    {
        $checked = boolval($this->form['send_confirmation'] ?? false);
        $this->showCheckbox('sendConfirmation', 'Stuur bovenstaande tekst als bevestiging', $checked);
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="email">E-mailadres: </label>
            <div class="col-sm-5">
                <input type="email" class="form-control" id="email" name="email" value="<?=$this->form['mailadres'] ?? '';?>" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="antiSpamAnswer">Antispamantwoord: </label>
            <div class="col-sm-5">
                <input type="text" class="form-control" id="antiSpamAnswer" name="antiSpamAnswer" value="<?=$this->form['antispamantwoord'] ?? '';?>" />
            </div>
        </div>
        <?php
    }
}