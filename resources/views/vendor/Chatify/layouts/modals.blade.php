{{-- ---------------------- Image modal box ---------------------- --}}
<div id="imageModalBox" class="imageModal">
    <span class="imageModal-close">&times;</span>
    <img class="imageModal-content" id="imageModalBoxSrc">
  </div>

  {{-- ---------------------- Delete Modal ---------------------- --}}
  <div class="app-modal" data-name="delete">
      <div class="app-modal-container">
          <div class="app-modal-card" data-name="delete" data-modal='0'>
              <div class="app-modal-header">Are you sure you want to delete this?</div>
              <div class="app-modal-body">You can not undo this action</div>
              <div class="app-modal-footer">
                  <a href="javascript:void(0)" class="app-btn cancel">Cancel</a>
                  <a href="javascript:void(0)" class="app-btn a-btn-danger delete">Delete</a>
              </div>
          </div>
      </div>
  </div>
  {{-- ---------------------- Alert Modal ---------------------- --}}
  <div class="app-modal" data-name="alert">
      <div class="app-modal-container">
          <div class="app-modal-card" data-name="alert" data-modal='0'>
              <div class="app-modal-header"></div>
              <div class="app-modal-body"></div>
              <div class="app-modal-footer">
                  <a href="javascript:void(0)" class="app-btn cancel">Cancel</a>
              </div>
          </div>
      </div>
  </div>
  {{-- ---------------------- Settings Modal ---------------------- --}}
  <div class="app-modal" data-name="settings">
      <div class="app-modal-container">
          <div class="app-modal-card" data-name="settings" data-modal='0'>
              <form id="update-settings" action="{{ route('avatar.update') }}" enctype="multipart/form-data" method="POST">
                  @csrf
                  {{-- <div class="app-modal-header">Update your profile settings</div> --}}
                  <div class="app-modal-body">
                      {{-- Udate profile avatar --}}
                      <div class="avatar av-l upload-avatar-preview chatify-d-flex"
                      style="background-image: url('{{ Chatify::getUserWithAvatar(Auth::user())->avatar }}');"
                      ></div>
                      <p class="upload-avatar-details"></p>
                      <label class="app-btn a-btn-primary update" style="background-color:{{$messengerColor}}">
                          Upload New
                          <input class="upload-avatar chatify-d-none" accept="image/*" name="avatar" type="file" />
                      </label>
                      {{-- Dark/Light Mode  --}}
                      <p class="divider"></p>
                      <p class="app-modal-header">Dark Mode <span class="
                        {{ Auth::user()->dark_mode > 0 ? 'fas' : 'far' }} fa-moon dark-mode-switch"
                         data-mode="{{ Auth::user()->dark_mode > 0 ? 1 : 0 }}"></span></p>
                      {{-- change messenger color  --}}
                      <p class="divider"></p>
                      {{-- <p class="app-modal-header">Change {{ config('chatify.name') }} Color</p> --}}
                      <div class="update-messengerColor">
                      @foreach (config('chatify.colors') as $color)
                        <span style="background-color: {{ $color}}" data-color="{{$color}}" class="color-btn"></span>
                        @if (($loop->index + 1) % 5 == 0)
                            <br/>
                        @endif
                      @endforeach
                      </div>
                  </div>
                  <div class="app-modal-footer">
                      <a href="javascript:void(0)" class="app-btn cancel">Cancel</a>
                      <input type="submit" class="app-btn a-btn-success update" value="Save Changes" />
                  </div>
              </form>
          </div>
      </div>
  </div>
  {{-- ---------------------- Create Group Modal ---------------------- --}}
  <div class="app-modal" data-name="create-group">
      <div class="app-modal-container">
          <div class="app-modal-card" data-name="create-group" data-modal="0">
              <form id="create-group-form" action="{{ route('chatify.group.create') }}" method="POST">
                  @csrf
                  <div class="app-modal-header">Create Group</div>
                  <div class="app-modal-body">
                      <div class="form-group">
                          <label>Group Name</label>
                          <input type="text" name="name" class="app-input" placeholder="e.g. Tim Marketing" required>
                      </div>
                      <p class="divider"></p>
                      <small class="text-muted d-block mb-2">Select members:</small>
                      <div class="group-members-list app-scroll">
                          @isset($chatifyUsers)
                              @forelse($chatifyUsers as $user)
                                  <label class="group-member-item">
                                      <input type="checkbox" name="member_ids[]" value="{{ $user->id }}">
                                      <span>{{ $user->name }}</span>
                                  </label>
                              @empty
                                  <div class="text-muted">Tidak ada user.</div>
                              @endforelse
                          @else
                              <div class="text-muted">Tidak ada user.</div>
                          @endisset
                      </div>
                      <div class="group-create-error"></div>
                  </div>
                  <div class="app-modal-footer">
                      <a href="javascript:void(0)" class="app-btn cancel">Cancel</a>
                      <button type="submit" class="app-btn a-btn-success">Create</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
