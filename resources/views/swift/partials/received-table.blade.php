{{-- resources/views/swift/partials/received-table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th>Référence</th>
                <th>Type</th>
                <th>Catégorie</th>
                <th>Émetteur</th>
                <th>Récepteur</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages as $msg)
                @if($msg->DIRECTION == 'IN')
                <tr>
                    <td><strong>{{ $msg->REFERENCE }}</strong></td>
                    <td><span class="badge bg-secondary">{{ $msg->TYPE_MESSAGE }}</span></td>
                    <td>
                        @php
                            $cat = $msg->CATEGORIE ?? $msg->determineCategorie();
                        @endphp
                        <span class="badge bg-info">{{ $cat }}</span>
                    </td>
                    <td>
                        <div>
                            <strong>{{ $msg->SENDER_NAME ?? 'N/A' }}</strong>
                            @if($msg->SENDER_BIC)
                                <br><small class="text-muted">{{ $msg->SENDER_BIC }}</small>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong>{{ $msg->RECEIVER_NAME ?? 'N/A' }}</strong>
                            @if($msg->RECEIVER_BIC)
                                <br><small class="text-muted">{{ $msg->RECEIVER_BIC }}</small>
                            @endif
                        </div>
                    </td>
                    <td class="fw-bold">{{ number_format($msg->AMOUNT, 2) }} {{ $msg->CURRENCY }}</td>
                    <td>
                        @if($msg->CREATED_AT)
                            {{ $msg->CREATED_AT->format('d/m/Y H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($msg->STATUS == 'pending')
                            <span class="badge bg-warning">En attente</span>
                        @elseif($msg->STATUS == 'processed')
                            <span class="badge bg-success">Traité</span>
                        @else
                            <span class="badge bg-secondary">{{ $msg->STATUS }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('swift.show', $msg->id) }}" class="btn btn-outline-info" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="openModal('mx', {{ $msg->id }})" title="View MX">
                                <i class="fas fa-code"></i>
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="openModal('mt', {{ $msg->id }})" title="View MT">
                                <i class="fas fa-file-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endif
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun message reçu trouvé</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>