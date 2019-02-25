<?php
declare (strict_types = 1);

namespace Cyndaron\Mailform;

class EditorPage extends \Cyndaron\EditorPage
{
    protected $type = 'mailform';
    protected $table = 'mailformulieren';
    protected $saveUrl = '/editor/mailform/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->form = Mailform::loadFromDatabase((int)$this->id)->asArray();
            $this->content = $this->form['tekst_bevestiging'];
            $this->titel = $this->form['naam'];
        }
    }

    protected function toonSpecifiekeKnoppen()
    {
        $checked = $this->form['stuur_bevestiging'] ? 'checked="checked"' : '';
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="sendConfirmation">Stuur bovenstaande tekst als bevestiging: </label>
            <div class="col-sm-5">
                <input id="sendConfirmation" name="sendConfirmation" type="checkbox" <?= $checked; ?>/>
            </div>
        </div>
        <?php
    }
}