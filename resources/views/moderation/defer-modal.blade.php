<div class="modal fade" id="deferModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('moderation.defer', $item->contentVersion) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Defer Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Assign to user</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            @foreach($moderators as $moderator)
                                <option value="{{ $moderator->id }}">{{ $moderator->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Deferral</button>
                </div>
            </form>
        </div>
    </div>
</div>