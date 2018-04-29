@extends('kit::backend.layouts.master')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="ibox ibox-table">
                <div class="ibox-title">
                    <h5><i class="fa fa-users"></i> {!! __('Attached Users') !!}</h5>
                    <div class="buttons">
                        <a id="detach-all-user"
                           href="{{route('backend.role.user.detach_all', ['role' => $role->id])}}"
                           class="btn btn-danger btn-xs">
                            <i class="fa fa-remove"></i> {{__('Detach all')}}
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="form-horizontal form-1-line">
                        <div class="form-group">
                            {!! Form::label('user_id', __('Add User'), ['class' => 'col-xs-3 control-label']) !!}
                            <div class="col-xs-9">
                                {!! Form::select('user_id', [], null, ['id' => 'user_id', 'class' => 'form-control select-user', 'placeholder' => __('Select User').'...']) !!}
                                <a id="attach-user"
                                   href="{{route('backend.role.user.attach', ['role' => $role->id, 'user' => '__ID__'])}}"
                                   class="btn btn-danger btn-block disabled"><i
                                            class="fa fa-plus"></i> {{__('Attach')}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <table class="table table-hover table-striped table-bordered table-detail table-users">
                        <thead>
                        <tr>
                            <th class="min-width">#</th>
                            <th>{{__('Fullname')}}</th>
                            <th class="min-width">{{__('Username')}}</th>
                            <th class="min-width">{{__('Group')}}</th>
                            <th class="min-width"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $i => $user)
                            <tr>
                                <td class="min-width">{{$i+1}}</td>
                                <td>{{$user->name}}</td>
                                <td class="min-width">{{$user->username}}</td>
                                <td class="min-width">{{$user->group->acronym_name}}</td>
                                <td class="min-width">
                                    <a href="{{route('backend.role.user.detach', ['role' => $role->id, 'user' => $user->id])}}"
                                       class="detach-user text-danger"
                                       data-toggle="tooltip"
                                       data-title="{{__('Detach')}}">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ibox ibox-table">
                <div class="ibox-title">
                    <h5><i class="fa fa-check"></i> {!! __('Attached Permissions') !!}</h5>
                    <div class="buttons">
                        <a id="detach-all-permission"
                           href="{{route('backend.role.permission.detach_all', ['role' => $role->id])}}"
                           class="btn btn-warning btn-xs">
                            <i class="fa fa-remove"></i> {{__('Detach all')}}
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="form-horizontal form-1-line">
                        <div class="form-group">
                            {!! Form::label('permission_id', __('Add Permission'), ['class' => 'col-xs-4 control-label']) !!}
                            <div class="col-xs-8">
                                {!! Form::select('permission_id', $selectize_permissions, null, ['id' => 'permission_id', 'class' => 'form-control', 'placeholder' => __('Select Permission').'...']) !!}
                                <a id="attach-permission"
                                   href="{{route('backend.role.permission.attach', ['role' => $role->id, 'permission' => '__ID__'])}}"
                                   class="btn btn-warning btn-block disabled">
                                    <i class="fa fa-plus"></i> {{__('Add')}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <table class="table table-hover table-striped table-bordered table-detail table-permissions">
                        <thead>
                        <tr>
                            <th class="min-width">#</th>
                            <th>{{__('Actions')}}</th>
                            <th class="min-width"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        @foreach($permissions as $model => $actions)
                            <tr>
                                <td></td>
                                <td colspan="2" class="text-uppercase text-info">{{$model}}</td>
                            </tr>
                            @foreach($actions as $id => $title)
                                <tr>
                                    <td>{{$i}}</td>
                                    <td><strong>{{$id}}</strong><span class="text-danger"> — {{$title}}</span></td>
                                    <td>
                                        <a href="{{route('backend.role.permission.detach', ['role' => $role->id, 'permission' => $id])}}"
                                           class="detach-permission text-danger"
                                           data-toggle="tooltip"
                                           data-title="{{__('Detach')}}">
                                            <i class="fa fa-remove"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $.fn.mbHelpers.reloadPage = function () {
            location.reload(true);
        };
        function updateState(element, enable) {
            enable ? $(element).removeClass('disabled') : $(element).addClass('disabled');
        }

        function attach(event, element, input) {
            event.preventDefault();
            let id = input.val(),
                url = $(element).attr('href');
            if (id && !$(element).hasClass('disabled')) {
                $.post(url.replace('__ID__', id), {_token: window.Laravel.csrfToken}, function (data) {
                    $.fn.mbHelpers.showMessage(data.type, data.content);
                    $.fn.mbHelpers.reloadPage();
                }, 'json');
            }
        }

        function detach(event, element, message, title, ids) {
            event.preventDefault();
            let _this = $(element);
            ids = ids || '';
            _this.tooltip('hide');
            window.bootbox.confirm({
                message: '<div class="message-delete"><div class="confirm">' + message + '</div></div>',
                title: '<i class="fa fa-remove"></i> ' + title,
                buttons: {
                    cancel: {label: '{{__("Cancel")}}', className: "btn-default btn-white"},
                    confirm: {label: '{{__("OK")}}', className: "btn-danger"}
                },
                callback: function (ok) {
                    if (ok) {
                        $.post(_this.attr('href').replace('__IDS__', ids), {
                            _token: window.Laravel.csrfToken,
                            _method: 'delete'
                        }, function (data) {
                            $.fn.mbHelpers.showMessage(data.type, data.content);
                            if (ids.length <= 0) {
                                _this.parents('tr').remove();
                            }
                            $.fn.mbHelpers.reloadPage();
                        }, 'json');
                    }
                }
            });
        }

        // User actions
        let user_id = $('#user_id'),
            attach_user = $('#attach-user');
        user_id.selectize_user({
            url: '{!! route('backend.user.select', ['query' => '__QUERY__']) !!}',
            users: {!! json_encode($selectize_users) !!},
            onChange: function (value) {
                updateState(attach_user, value);
            }
        });

        attach_user.click(function (e) {
            attach(e, this, user_id);
        });

        $('a.detach-user').click(function (e) {
            detach(
                e,
                this,
                '{{__("Do you really want to detach this User?")}}',
                '{{__("Detach User")}}'
            );
        });
        $('#detach-all-user').click(function (e) {
            detach(
                e,
                this,
                '{{__("Do you really want to detach all Users?")}}',
                '{{__("Detach all")}}'
            );
        });

        // Permission actions
        let permission_id = $('#permission_id'),
            attach_permission = $('#attach-permission');
        ;
        permission_id.selectize({
            persist: false,
            create: false,
            createOnBlur: false,
            searchField: ['text', 'value'],
            render: {
                option: function (item) {
                    return '<div><strong>' + item.value + '</strong><span class="text-danger"> — ' + item.text + '</span>' + '</div>';
                },
                item: function (item) {
                    return '<div><strong>' + item.value + '</strong><span class="text-danger"> — ' + item.text + ' ' + item.optgroup + '</span>' + '</div>';
                }
            },
            onChange: function (value) {
                updateState(attach_permission, value);
            }
        });

        attach_permission.click(function (e) {
            attach(e, this, permission_id);
        });
        $('a.detach-permission').click(function (e) {
            detach(
                e,
                this,
                '{{__("Do you really want to detach this Permission?")}}',
                '{{__("Detach Permission")}}'
            );
        });
        $('#detach-all-permission').click(function (e) {
            detach(
                e,
                this,
                '{{__("Do you really want to detach all Permissions?")}}',
                '{{__("Detach all")}}'
            );
        });
    });
</script>
@endpush