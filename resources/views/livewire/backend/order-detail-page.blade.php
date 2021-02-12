<div class="medium-container">

    @if (session()->has('error'))
        <x-alert type="danger" dismissible>{{ session()->get('error') }}</x-alert>
    @endif
    @if (session()->has('message'))
        <x-alert type="success" dismissible>{{ session()->get('message') }}</x-alert>
    @endif

    {{-- Order details --}}
    <x-card title="Order #{{ $order->id }}">
        <x-slot name="addon">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <strong>Status:</strong>
                    <x-order-status-label :order="$order" />
                </li>
                <li class="list-group-item">
                    <strong>Customer:</strong>
                    @isset($order->customer)
                        <a href="{{ route('backend.customers.show', $order->customer) }}">{{ $order->customer->name }}</a>
                        ({{ $order->customer->id_number }})
                    @else
                        <em>Deleted</em>
                    @endisset
                </li>
                <li class="list-group-item">
                    <strong>IP Address:</strong>
                    <x-ip-info :value="$order->ip_address" />
                    <br>
                    <strong>Geo Location:</strong>
                    <x-geo-location-info :value="$order->ip_address" />
                    <br>
                    <strong>User Agent:</strong>
                    <x-user-agent-info :value="$order->user_agent" />
                </li>
                <li class="list-group-item">
                    <strong>Registered:</strong>
                    <x-date-time-info :value="$order->created_at" />
                </li>
            </ul>
        </x-slot>
    </x-card>

    {{-- Remarks --}}
    @isset($order->remarks)
        <x-alert type="info mb-4">
            <strong>Remarks from customer:</strong><br>
            {!! nl2br(e($order->remarks)) !!}
        </x-alert>
    @endisset

    {{-- Products --}}
    <h3>Products</h3>
    <div class="table-responsive mb-4">
        <table class="table table-bordered bg-white shadow-sm mb-0">
            @php
                $hasPictures = $order->products->whereNotNull('pictureUrl')->isNotEmpty();
            @endphp
            <thead>
                <tr>
                    <th @if ($hasPictures) colspan="2" @endif>Product</th>
                    <th class="text-end">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->products->sortBy('name') as $product)
                    <tr>
                        @if ($hasPictures)
                            <td class="fit">
                                @isset($product->pictureUrl)
                                    <img
                                        src="{{ $product->pictureUrl }}"
                                        alt="Product Image"
                                        style="max-width: 100px; max-height: 75px" />
                                @endisset
                            </td>
                        @endif
                        <td>
                            {{ $product->name }}<br>
                            <small>{{ $product->category }}</small>
                        </td>
                        <td class="fit text-end align-middle">
                            <strong><big>{{ $product->pivot->quantity }}</big></strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Order history --}}
    @php
        $audits = $order->audits()->with('user')->get();
    @endphp
    @if ($audits->isNotEmpty())
        <h3 class="mt-2">Order history</h3>
        <ul class="list-group shadow-sm mb-4">
            @foreach ($audits as $audit)
                <li class="list-group-item">
                    On <strong>
                        <x-date-time-info :value="$audit->created_at" />
                    </strong>
                    <strong>{{ optional($audit->user)->name ?? 'Unknown' }}</strong>
                    @if ($audit->event == 'created')
                        registered the order.
                    @elseif($audit->event == 'updated')
                        updated the order and changed
                        @php
                        $modified = $audit->getModified();
                        @endphp
                        @foreach ($modified as $key => $val)
                            <em>{{ $key }}</em> from <code>{{ $val['old'] }}</code> to
                            <code>{{ $val['new'] }}</code>@if ($loop->last).@else,@endif
                        @endforeach
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Change status --}}
    @if($showChangeStatus)
        <x-card title="Change order #{{ $order->id }}">
            <p class="form-label">New status:</p>
            @foreach($this->statuses as $key)
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="radio"
                        id="newStatusInput_{{ $key }}"
                        @if($key == $order->status) autofocus @endif
                        value="{{ $key }}"
                        wire:model="newStatus">
                    <label class="form-check-label" for="newStatusInput_{{ $key }}">
                        <x-order-status-label :value="$key" />
                        @if($key == $order->status)
                            (current)
                        @endif
                    </label>
                </div>
            @endforeach
            <x-slot name="footer">
                <button
                    type="submit"
                    class="btn btn-primary"
                    wire:target="submit"
                    wire:loading.attr="disabled"
                    wire:click="submit">
                    <x-spinner wire:loading wire:target="submit"/>
                    Apply
                </button>
            </x-slot>
        </x-card>
    @endif

    {{-- Buttons --}}
    <div class="d-flex justify-content-between mb-3">
        <span>
            @if(!$showChangeStatus && $order->isOpen)
                <button
                    wire:click="$set('showChangeStatus', true)"
                    class="btn btn-primary">
                    Change
                </button>
            @endif
        </span>
        <a
            href="{{ route('backend.orders') }}"
            class="btn btn-link">
            Back to overview
        </a>
    </div>
</div>
