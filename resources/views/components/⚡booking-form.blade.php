<?php

use Livewire\Component;
use App\Models\Room;
use App\Models\Item;
use App\ItemState;

new class extends Component {
    public $reservable_type = 'room';
    public $selected_reservable;
    public $rooms = [];
    public $items = [];
    public $selectedResources = [];
    public $invalidResources = [];
    public $message = null;
    public $quantity = 1;
    public $booking;

    public function mount($booking = null)
    {
        // cargar solo activos
        $this->rooms = Room::where('active', true)->get();

        $this->items = Item::where('active', true)
            ->where('state', ItemState::ok)
            ->get();

        if ($booking) {

            $this->booking = $booking;

            // añadir también los que ya están en la reserva
            foreach ($booking->bookingItems as $b) {

                if ($b->reservable_type === Room::class) {

                    $room = Room::find($b->reservable_id);

                    if ($room && !$this->rooms->contains('id', $room->id)) {
                        $this->rooms->push($room);
                    }

                } else {

                    $item = Item::find($b->reservable_id);

                    if ($item && !$this->items->contains('id', $item->id)) {
                        $this->items->push($item);
                    }

                }
            }

            // recursos seleccionados (esto sí va con toArray)
            $this->selectedResources = $booking->bookingItems->map(function ($b) {

                $type = $b->reservable_type === Room::class ? 'room' : 'item';

                $quantity = $type === 'item' ? $b->quantity : 1;

                $model = $type === 'item'
                    ? Item::find($b->reservable_id)
                    : Room::find($b->reservable_id);

                return [
                    'type' => $type,
                    'id' => $b->reservable_id,
                    'name' => $model?->name ?? '???',
                    'quantity' => $quantity,
                    'active' => $model?->active ?? false,
                    'state' => $type === 'item' ? $model?->state?->value : null,
                ];

            })->toArray();
            $this->invalidResources = [];

            foreach ($this->selectedResources as $res) {

                if ($res['type'] === 'room') {

                    $room = Room::find($res['id']);

                    if (!$room || !$room->active) {
                        $this->invalidResources[] = $res['name'];
                    }

                }

                if ($res['type'] === 'item') {

                    $item = Item::find($res['id']);

                    if (
                        !$item ||
                        !$item->active ||
                        $item->state !== ItemState::ok
                    ) {
                        $this->invalidResources[] = $res['name'];
                    }

                }
            }
        }
        if (old('resources')) {

            $this->selectedResources = [];

            foreach (old('resources') as $res) {

                if ($res['type'] === 'room') {

                    $room = Room::find($res['id']);

                    if ($room) {
                        $this->selectedResources[] = [
                            'type' => 'room',
                            'id' => $room->id,
                            'name' => $room->name,
                            'quantity' => 1,
                            'active' => $room->active,
                            'state' => null,
                        ];
                    }

                }

                if ($res['type'] === 'item') {

                    $item = Item::find($res['id']);

                    if ($item) {
                        $this->selectedResources[] = [
                            'type' => 'item',
                            'id' => $item->id,
                            'name' => $item->name,
                            'quantity' => $res['quantity'] ?? 1,
                            'active' => $item->active,
                            'state' => $item->state->value,
                        ];
                    }

                }

            }

        }
    }

    public function addResource()
    {
        $type = $this->reservable_type;
        $id = $this->selected_reservable;
        $quantityToAdd = $this->quantity ?: 1;

        if ($type === 'room') {
            $model = Room::find($id);

            // Solo se permite añadir una vez
            if (collect($this->selectedResources)->contains(fn($res) => $res['type'] === 'room' && $res['id'] == $id)) {
                $this->message = "No se puede añadir dos veces la misma sala.";
                return;
            }

            $this->selectedResources[] = [
                'type' => 'room',
                'id' => $model->id,
                'name' => $model->name,
                'quantity' => 1,
                'active' => $model->active,
                'state' => null,
            ];

        } else {
            $model = Item::find($id);

            // Cantidad ya seleccionada
            $existingIndex = collect($this->selectedResources)
                ->search(fn($res) => $res['type'] === 'item' && $res['id'] == $id);

            $alreadySelected = $existingIndex !== false ? $this->selectedResources[$existingIndex]['quantity'] : 0;

            if ($quantityToAdd + $alreadySelected > $model->quantity) {
                $this->message = "No puedes reservar más de {$model->quantity} unidades de {$model->name}.";
                return;
            }

            if ($existingIndex !== false) {
                // Sumar a la cantidad existente
                $this->selectedResources[$existingIndex]['quantity'] += $quantityToAdd;


                $this->selectedResources[$existingIndex]['active'] = $model->active;
                $this->selectedResources[$existingIndex]['state'] = $model->state->value;
            } else {
                $this->selectedResources[] = [
                    'type' => 'item',
                    'id' => $model->id,
                    'name' => $model->name,
                    'quantity' => $quantityToAdd,
                    'active' => $model->active,
                    'state' => $model->state->value,
                ];
            }
        }

        // Reset campos
        $this->quantity = 1;
        $this->selected_reservable = null;
        $this->message = null;
    }

    public function removeResource($index)
    {
        unset($this->selectedResources[$index]);
        $this->selectedResources = array_values($this->selectedResources);
    }

    public function updatedReservableType()
    {
        $this->quantity = 1;
    }

    public function getAvailableQuantityProperty()
    {
        if ($this->reservable_type !== 'item' || !$this->selected_reservable)
            return null;
        $item = Item::find($this->selected_reservable);
        if (!$item)
            return null;

        $alreadySelected = collect($this->selectedResources)
            ->where('type', 'item')
            ->where('id', $this->selected_reservable)
            ->sum('quantity');

        return $item->quantity - $alreadySelected;
    }
};
?>

<div>

{{--     <label>Tipo</label>
    <select wire:model.live="reservable_type">
        <option value="room">Sala</option>
        <option value="item">Objeto</option>
    </select> --}}
    <flux:radio.group wire:model.live="reservable_type" label="Tipo de recurso" variant="segmented">
        <flux:radio value="room" label="Sala" icon="building-office"/>
        <flux:radio value="item" label="Equipo" icon="computer-desktop"/>
    </flux:radio.group>

    <label>Recurso</label>
    <flux:select wire:model.live="selected_reservable">
        <option value="">--</option>
        @if($reservable_type === 'room')
            @foreach($rooms as $room)
                <option value="{{ $room->id }}">{{ $room->name }}</option>
            @endforeach
        @else
            @foreach($items as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        @endif
    </flux:select>

    @if($reservable_type === 'item' && $selected_reservable)
        <label class="mt-2">
            Cantidad (disponible: {{ $this->availableQuantity }})
            <input type="number" min="1" max="{{ $this->availableQuantity }}" wire:model.live="quantity" class="border p-1">
        </label>
    @endif

    <div class="mt-3 mb-3">
        <flux:button variant="primary" wire:click="addResource">
            Añadir
        </flux:button>
    </div>

    @if($message)
        <p class="text-red-500">{{ $message }}</p>
    @endif



    <h3>Recursos añadidos</h3>

    <ul>

        @foreach($selectedResources as $index => $res)

            <li>
                {{ $res['name'] }}

                @if(!$res['active'])
                    <span class="text-red-500"> (inactivo)</span>
                @endif

                @if($res['type'] === 'item' && $res['state'] !== 'ok')
                    <span class="text-red-500">
                        ({{ $res['state'] }})
                    </span>
                @endif

                @if($res['type'] === 'item')
                    (x{{ $res['quantity'] }})
                @endif
                <flux:button variant="danger" wire:click="removeResource({{ $index }})" size="sm">
                    X
                </flux:button>
            </li>

        @endforeach
        @if(count($invalidResources))
            <div class="text-red-600 mt-3">
                ⚠ Algunos recursos no están disponibles:

                <ul>
                    @foreach($invalidResources as $name)
                        <li>{{ $name }}</li>
                    @endforeach
                </ul>

                Contacta con un administrador o cambia los recursos.
            </div>
        @endif

    </ul>


    {{-- hidden para form normal --}}

    @foreach($selectedResources as $i => $res)

        <input type="hidden" name="resources[{{ $i }}][type]" value="{{ $res['type'] }}">

        <input type="hidden" name="resources[{{ $i }}][id]" value="{{ $res['id'] }}">
        <input type="hidden" name="resources[{{ $i }}][quantity]" value="{{ $res['quantity'] }}">

    @endforeach
    @if(session('tmp_attachments'))

    <div class="mt-3">

        <b>Archivos añadidos:</b>

        @foreach(session('tmp_attachments') as $file)

            <div>
                {{ $file['original_name'] }}
            </div>

        @endforeach

    </div>

    @endif


</div>
<script>
    Livewire.on('swal', data => {
        Swal.fire(data); // SweetAlert debe estar cargado en la página
    });
</script>