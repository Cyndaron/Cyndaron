@include('View/Widget/Form/BasicInput', ['id' => $id, 'type' => 'datetime-local', 'label' => $label, 'value' => $value?->format('Y-m-d H:i') ?? ''])
