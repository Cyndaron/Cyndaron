@include('View/Widget/Form/BasicInput', ['inputType' => 'number', 'id' => $id, 'label' => $label, 'value' => $value ?? '', 'min' => $min ?? 0, 'max' => $max ?? '', 'step' => $step ?? 1])
