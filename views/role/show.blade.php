@extends('backend.layouts.master')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="ibox ibox-table">
                <div class="ibox-title">
                    <h5>{!! trans('authority::common.attached_users') !!}</h5>
                    <div class="ibox-tools">
                        <a id="detach-all-user" href="{{route('backend.role.user.detach_all', ['role' => $role->id])}}"
                           class="btn btn-danger btn-xs">
                            <span class="fa fa-remove"></span> {{trans('authority::common.detach_all')}}
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="form-horizontal form-1-line">
                        <div class="form-group">
                            {!! Form::label('user_id', trans('authority::common.add_user'), ['class' => 'col-xs-3 control-label']) !!}
                            <div class="col-xs-9">
                                {!! Form::select('user_id', [], null, ['id' => 'user_id', 'class' => 'form-control select-user', 'placeholder' => trans('user::user.select_user').'...']) !!}
                                <a id="attach-user"
                                   href="{{route('backend.role.user.attach', ['role' => $role->id, 'user' => '__ID__'])}}"
                                   class="btn btn-primary btn-block disabled"><i
                                            class="fa fa-plus"></i> {{trans('authority::common.attach')}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <table class="table table-hover table-striped table-bordered table-detail table-users">
                        <thead>
                        <tr>
                            <th class="min-width">#</th>
                            <th>{{trans('user::user.name')}}</th>
                            <th class="min-width">{{trans('user::user.username_th')}}</th>
                            <th class="min-width">{{trans('user::user.group_id')}}</th>
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
                                       data-title="{{trans('authority::common.detach')}}">
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
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $.fn.mbHelpers.reloadPage = function () {
            location.reload(true);
        };
        let user_id = $('#user_id'),
            attach_user = $('#attach-user');
        user_id.selectize_user({
            url: '{!! route('backend.user.select', ['query' => '__QUERY__']) !!}',
            users: {!! json_encode($selectize_users) !!},
            onChange: function (value) {
                if (value) {
                    attach_user.removeClass('disabled');
                } else {
                    attach_user.addClass('disabled');
                }
            }
        });
        attach_user.click(function (e) {
            e.preventDefault();
            let id = user_id.val(),
                url = attach_user.attr('href');
            if (attach_user.hasClass('disabled') || id <= 0) {
                return;
            }
            $.post(url.replace('__ID__', id), {_token: window.Laravel.csrfToken}, function (data) {
                $.fn.mbHelpers.showMessage(data.type, data.content);
                $.fn.mbHelpers.reloadPage();
            }, 'json');
        });

        function detach_action(element, message, title, ids) {
            let _this = $(element);
            ids = ids || '';
            _this.tooltip('hide');
            window.bootbox.confirm({
                message: '<div class="message-delete"><div class="confirm">' + message + '</div></div>',
                title: '<i class="fa fa-remove"></i> ' + title,
                buttons: {
                    cancel: {label: '{{trans("common.cancel")}}', className: "btn-default btn-white"},
                    confirm: {label: '{{trans("common.ok")}}', className: "btn-danger"}
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

        $('a.detach-user').click(function (e) {
            e.preventDefault();
            detach_action(
                this,
                '{{trans("authority::common.detach_user_confirm")}}',
                '{{trans("authority::common.detach_user")}}'
            );
        });

        $('#detach-all-user').click(function (e) {
            e.preventDefault();
            detach_action(
                this,
                '{{trans("authority::common.detach_all_user_confirm")}}',
                '{{trans("authority::common.detach_all")}}'
            );
        });

        $('#sync-permission').click(function (e) {
            e.preventDefault();
            let _this = $(this);
            _this.tooltip('hide');
            $.post(_this.attr('href'), {_token: window.Laravel.csrfToken}, function (data) {
                $.fn.mbHelpers.showMessage(data.type, data.content);
                $.fn.mbHelpers.reloadPage();
            }, 'json');
        });

        $('.attach-permission').click(function (e) {
            e.preventDefault();
            let _this = $(this),
                ids = [];
            _this.tooltip('hide');
            _this.parents('tr').find('input[type="checkbox"]:checked').each(function () {
                ids.push($(this).data('id'));
            });
            if (ids.length) {
                $.post(_this.attr('href').replace('__IDS__', ids.join(',')), {_token: window.Laravel.csrfToken}, function (data) {
                    $.fn.mbHelpers.showMessage(data.type, data.content);
                    $.fn.mbHelpers.reloadPage();
                }, 'json');
            }
        });

        $('a.detach-permission').click(function (e) {
            e.preventDefault();
            let _this = $(this),
                ids = [];
            _this.parents('tr').find('input[type="checkbox"]:checked').each(function () {
                ids.push($(this).data('id'));
            });
            if (ids.length) {
                detach_action(
                    this,
                    '{{trans("authority::common.detach_permission_confirm")}}',
                    '{{trans("authority::common.detach_permission")}}',
                    ids.join(',')
                );
            } else {
                _this.tooltip('hide');
            }
        });

        $('#detach-all-permission').click(function (e) {
            e.preventDefault();
            detach_action(
                this,
                '{{trans("authority::common.detach_all_permission_confirm")}}',
                '{{trans("authority::common.detach_all")}}'
            );
        });
    });
</script>
@endpush