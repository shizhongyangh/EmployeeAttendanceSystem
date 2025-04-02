$(document).ready(function() {
    // 侧边栏切换
    $('#sidebarCollapse').on('click', function() {
        $('.sidebar').toggleClass('active');
        $('.content').toggleClass('active');
    });

    // 打卡功能
    $('#clockInBtn').on('click', function() {
        $.ajax({
            url: 'ajax/clock_in.php',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    });

    // 补卡功能
    $('.makeup-btn').on('click', function() {
        var date = $(this).data('date');
        
        $.ajax({
            url: 'ajax/makeup_clock.php',
            type: 'POST',
            data: {date: date},
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    });

    // 管理员补卡功能
    $('.admin-makeup-btn').on('click', function() {
        var userId = $(this).data('user');
        var date = $(this).data('date');
        
        $.ajax({
            url: 'ajax/admin_makeup_clock.php',
            type: 'POST',
            data: {user_id: userId, date: date},
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    });

    // 修改密码
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        var oldPassword = $('#oldPassword').val();
        var newPassword = $('#newPassword').val();
        var confirmPassword = $('#confirmPassword').val();
        
        if (newPassword !== confirmPassword) {
            showAlert('danger', '新密码和确认密码不一致');
            return;
        }
        
        $.ajax({
            url: 'ajax/change_password.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    $('#changePasswordForm')[0].reset();
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    });

    // 修改用户名
    $('#changeUsernameForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/change_username.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    });

    // 添加用户
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        
        var password = $('#password').val();
        var confirmPassword = $('#confirmPassword').val();
        
        if (password !== confirmPassword) {
            showAlert('danger', '密码和确认密码不一致');
            return;
        }
        
        $.ajax({
            url: 'ajax/add_user.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    $('#addUserForm')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    });

    // 删除用户
    $('.delete-user-btn').on('click', function() {
        var userId = $(this).data('id');
        
        if (confirm('确定要删除该用户吗？此操作不可恢复！')) {
            $.ajax({
                url: 'ajax/delete_user.php',
                type: 'POST',
                data: {user_id: userId},
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        showAlert('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', '系统错误，请稍后再试');
                }
            });
        }
    });

    // 编辑用户
    $('.edit-user-btn').on('click', function() {
        var userId = $(this).data('id');
        var username = $(this).data('username');
        var role = $(this).data('role');
        var points = $(this).data('points');
        
        $('#editUserId').val(userId);
        $('#editUsername').val(username);
        $('#editRole').val(role);
        $('#editPoints').val(points);
        
        $('#editUserModal').modal('show');
    });

    // 提交编辑用户表单
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/edit_user.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    $('#editUserModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    });

    // 申请请假
    $('#leaveForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/apply_leave.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    $('#leaveForm')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    });

    // 申请出差
    $('#businessTripForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/apply_business_trip.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    $('#businessTripForm')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    });

    // 审批请假
    $('.approve-leave-btn').on('click', function() {
        var leaveId = $(this).data('id');
        var status = 'approved';
        
        approveLeave(leaveId, status);
    });

    // 拒绝请假
    $('.reject-leave-btn').on('click', function() {
        var leaveId = $(this).data('id');
        var status = 'rejected';
        
        approveLeave(leaveId, status);
    });

    // 审批出差
    $('.approve-trip-btn').on('click', function() {
        var tripId = $(this).data('id');
        var status = 'approved';
        
        approveBusinessTrip(tripId, status);
    });

    // 拒绝出差
    $('.reject-trip-btn').on('click', function() {
        var tripId = $(this).data('id');
        var status = 'rejected';
        
        approveBusinessTrip(tripId, status);
    });

    // 显示提示信息
    function showAlert(type, message) {
        var alertHtml = '<div class="alert alert-' + type + '">' + message + '</div>';
        $('.alert-container').html(alertHtml);
        
        // 3秒后自动消失
        setTimeout(function() {
            $('.alert-container .alert').fadeOut();
        }, 3000);
    }

    // 审批请假
    function approveLeave(leaveId, status) {
        var comment = prompt('请输入审批意见：');
        
        $.ajax({
            url: 'ajax/approve_leave.php',
            type: 'POST',
            data: {leave_id: leaveId, status: status, comment: comment},
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    }

    // 审批出差
    function approveBusinessTrip(tripId, status) {
        var comment = prompt('请输入审批意见：');
        
        $.ajax({
            url: 'ajax/approve_business_trip.php',
            type: 'POST',
            data: {trip_id: tripId, status: status, comment: comment},
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', '系统错误，请稍后再试');
            }
        });
    }

    // 日期选择器
    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            language: 'zh-CN'
        });
    }

    // 模态框
    $('.modal-trigger').on('click', function() {
        var target = $(this).data('target');
        $(target).modal('show');
    });

    $('.modal .close').on('click', function() {
        $(this).closest('.modal').modal('hide');
    });

    // 自定义模态框显示和隐藏
    $.fn.modal = function(action) {
        if (action === 'show') {
            this.css('display', 'block');
        } else if (action === 'hide') {
            this.css('display', 'none');
        }
        return this;
    };

    // 月份选择
    $('#monthSelector').on('change', function() {
        var month = $(this).val();
        window.location.href = 'attendance.php?month=' + month;
    });

    // 年份选择
    $('#yearSelector').on('change', function() {
        var year = $(this).val();
        var month = $('#monthSelector').val();
        window.location.href = 'attendance.php?year=' + year + '&month=' + month;
    });
});
