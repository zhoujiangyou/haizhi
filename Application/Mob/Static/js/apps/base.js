/**
 * Created by Administrator on 2015-5-23.*/
$(function () {
    follower.bind_follow();
    goback();//绑定后退事件
});


//弹窗评论
var comment = function () {
    $('.atcomment').magnificPopup({
        type: 'ajax',
        overflowY: 'scroll',
        modal: true,
        callbacks: {
            ajaxContentAdded: function () {
                console.log(this.content);
            }
        }
    });
}

var addcomment = function () {

    $('#cancel').click(function () {
        $('.mfp-close').click();
    });
    $('#confirm').click(function () {
        var data = $("#at_comment").serialize();
        var url = $("#at_comment").attr('data-url');

        $.post(url, data, function (msg) {
            if (msg.status == 1) {
                $('.mfp-close').click();
                $(".addmore").prepend(msg.html);
                toast.success('评论成功!');
                del();
                comment();
            } else {
                toast.error(msg.info);
            }
        }, 'json');
    })
};


//以下都是表情包
var insertFace = function (obj) {
    var url = obj.attr('data-url');
    $('.XT_insert').css('z-index', '1000');
    $('.XT_face').remove();
    var html = '<div class="XT_face  XT_insert"><div class="triangle sanjiao"></div><div class="triangle_up sanjiao"></div>' +
        '<div class="XT_face_main"><div class="XT_face_title"><span class="XT_face_bt" style="float: left">常用表情</span>' +
        '<a onclick="close_face()" class="XT_face_close">X</a></div><div id="face" style="padding: 10px;"></div></div></div>';
    obj.parents('.weibo_post_box').find('#emot_content').html(html);
    getFace(obj.parents('.weibo_post_box').find('#emot_content'), 'miniblog', url);
};
var face_chose = function (obj) {
    var textarea = obj.parents('.weibo_post_box').find('textarea');

    if (textarea.attr('disabled') == 'disabled') {
        return false;
    }

    textarea.focus();
    textarea.val(textarea.val() + '[' + obj.attr('title') + ']');

    var pos = getCursortPosition(textarea[0]);
    var s = textarea.val();
    if (obj.attr('data-type') == 'miniblog') {
        textarea.val(s.substring(0, pos) + '[' + obj.attr('title') + ']' + s.substring(pos));
        setCaretPosition(textarea[0], pos + 2 + obj.attr('title').length);
    } else {
        textarea.val(s.substring(0, pos) + '[' + obj.attr('title') + ':' + obj.attr('data-type') + ']' + s.substring(pos));
        setCaretPosition(textarea[0], pos + 3 + obj.attr('title').length + obj.attr('data-type').length);
    }


}
var getFace = function (obj, miniblog, url) {
    $.post(url, {pkg: 'miniblog'}, function (res) {
        var expression = res.expression;
        var _imgHtml = '';
        if (miniblog.length > 0) {
            for (var k in expression) {
                _imgHtml += '<a href="javascript:void(0)" data-type="' + expression[k].type + '" title="' + expression[k].title + '" onclick="face_chose($(this))";><img src="' + expression[k].src + '" width="24" height="24" /></a>';
            }
            _imgHtml += '<div class="c"></div>';
        } else {
            _imgHtml = '获取表情失败';
        }
        obj.find('#face').html(_imgHtml);


    }, 'json');
};
var close_face = function () {
    $('.XT_face').remove();
};

//上传单张图片
var add_one_imgl = function () {
    $('#fileloadone').fileupload({
        done: function (e, result) {
            var $fileInputone = $(this);
            var src = result.result.data.file.path;
            var ids = $('#one_img_id').val(result.result.data.file.id);


            if (!ids == null) {
                $('.show_cover').hide();
            } else {
                $('.show_cover').show();
            }

            $("#cover_url").html('');
            $("#cover_url").html('<img src="' + src + '"style="width:72px;height:72px"  data-role="issue_cover" >');
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar').css(
                'width',
                progress + '%'
            );
        }
    });
};


//阅读消息
var Notify = {
    'readMessage': function (obj, message_id) {
        var url = $(obj).attr('data-url');
        var hrefurl = $(obj).attr('href')
        $.post(url, {message_id: message_id}, function (msg) {
            location.href = hrefurl;
        }, 'json');
    },
    /**
     * 将所有的消息设为已读
     */
    'setAllReaded': function (obj) {
        var url = $(obj).attr('data-url');
        $.post(url, {}, function () {
            toast.success('操作成功');
            setTimeout(function () {
                window.location.reload();
            }, 1000);

        });
    }
};

var follower = {
    'bind_follow': function () {
        $('[data-role="follow"]').unbind('click')
        $('[data-role="follow"]').click(function () {
            var $this = $(this);
            var uid = $this.attr('data-follow-who');
            $.post(U('Core/Public/follow'), {uid: uid}, function (msg) {
                if (msg.status) {
                    $this.attr('class', $this.attr('data-before'));
                    $this.attr('data-role', 'unfollow');
                    $this.html('已关注');
                    follower.bind_follow();
                    toast.success(msg.info, L('_KINDLY_REMINDER_'));
                } else {
                    toast.error(msg.info, L('_KINDLY_REMINDER_'));
                }
            }, 'json');
        })

        $('[data-role="unfollow"]').unbind('click')
        $('[data-role="unfollow"]').click(function () {
            var $this = $(this);
            var uid = $this.attr('data-follow-who');
            $.post(U('Core/Public/unfollow'), {uid: uid}, function (msg) {
                if (msg.status) {
                    $this.attr('class', $this.attr('data-after'));
                    $this.attr('data-role', 'follow');
                    $this.html('关注');
                    follower.bind_follow();
                    toast.success(msg.info, L('_KINDLY_REMINDER_'));
                } else {
                    toast.error(msg.info, L('_KINDLY_REMINDER_'));
                }
            }, 'json');
        })
    }
}
var goback = function () {
    $('#goback').click(function () {
        var need_confirm = $(this).attr('need-confirm');
        if (need_confirm) {
            var confirm_info = $(this).attr('confirm-info');
            if (confirm(confirm_info)) {
                history.go(-1);
            }
        } else {
            history.go(-1);
        }
    });
}


function U(url, params, rewrite) {


    if (window.Think.MODEL[0] == 2) {

        var website = _ROOT_ + '/';
        url = url.split('/');

        if (url[0] == '' || url[0] == '@')
            url[0] = APPNAME;
        if (!url[1])
            url[1] = 'Index';
        if (!url[2])
            url[2] = 'index';
        website = website + '' + url[0] + '/' + url[1] + '/' + url[2];

        if (params) {
            params = params.join('/');
            website = website + '/' + params;
        }
        if (!rewrite) {
            website = website + '.html';
        }

    } else {
        var website = _ROOT_ + '/index.php';
        url = url.split('/');
        if (url[0] == '' || url[0] == '@')
            url[0] = APPNAME;
        if (!url[1])
            url[1] = 'Index';
        if (!url[2])
            url[2] = 'index';
        website = website + '?s=/' + url[0] + '/' + url[1] + '/' + url[2];
        if (params) {
            params = params.join('/');
            website = website + '/' + params;
        }
        if (!rewrite) {
            website = website + '.html';
        }
    }

    if (typeof (window.Think.MODEL[1]) != 'undefined') {
        website = website.toLowerCase();
    }
    return website;
}


$(function () {
    local_comment_page_count = 2;
    bind_local_comment();
})


var bind_local_comment = function () {

    $('[data-role="do_local_comment"]').unbind('click');
    $('[data-role="do_local_comment"]').click(function () {
        var $this = $(this);
        var $textarea = $this.closest('.weibo_post_box').find('textarea');
        var url = $this.attr('data-url');
        var this_url = $this.attr('data-this-url');
        var path = $this.attr('data-path');
        var content = $textarea.val();
        var extra = $this.attr('data-extra');
        $.post(url, {content: content, path: path, this_url: this_url, extra: extra}, function (res) {
            if (res.status) {
                $textarea.val('');
                $('.localcomment_list').prepend(res.data);
                toast.success('发布成功');
            } else {
                toast.error('发布失败');
            }
            bind_local_comment();
        }, 'json');
    })


    $('[data-role="reply_local_comment"]').unbind('click');
    $('[data-role="reply_local_comment"]').click(function () {
        var $this = $(this);
        var $textarea = $('.weibo_post_box').find('textarea');
        var nickname = $this.attr('data-nickname');
        if ($textarea.attr('disabled') == 'disabled') {
            $textarea.val("")
        } else {
            $textarea.val("").focus().val('回复 @' + nickname + ' ：');
        }

        bind_local_comment();
    })

    $('[data-role="del_local_comment"]').unbind('click');
    $('[data-role="del_local_comment"]').click(function () {
        var $this = $(this);
        var id = $this.attr('data-id');

        var count_model = $this.attr('data-count-model');
        var count_field = $this.attr('data-count-field');

        var url = U('mob/base/dellocalcomment');

        $.post(url, {id: id, count_model: count_model, count_field: count_field}, function (res) {
            if (res.status) {
                $this.closest('.comment-item').fadeOut();
                toast.success('删除成功');
            } else {
                toast.error('删除失败');
            }
        }, 'json');
        bind_local_comment();
    })


    $('[data-role="show_more_localcomment"]').unbind('click');
    $('[data-role="show_more_localcomment"]').click(function () {
        var $this = $(this);
        var path = $this.attr('data-path');
        var url = U('mob/base/getLocalCommentList');
        $.post(url, {path: path, page: local_comment_page_count}, function (res) {
            if (res) {
                $('.localcomment_list').append(res);
                local_comment_page_count++;
            } else {
                toast.error('没有更多了');
            }

        }, 'json');
        bind_local_comment();
    })


}

//新上传图片
function add_img() {
    var filechooser = document.getElementById("choose");
    $("#upload").on("click", function () {
        filechooser.click();
    })
    filechooser.onchange = function () {
        if (!this.files.length) return;
        var files = Array.prototype.slice.call(this.files);
        if (files.length > 9) {
            alert("最多同时只可上传9张图片");
            return;
        }
        files.forEach(function (files, i) {
            if (!/\/(?:jpeg|png|gif)/i.test(files.type)){
                toast.error('上传图片格式不符！');
            }
            var div = ' <li class="waitbox loadingBox">\
            <img src= ' + _LOADING_ + '  style="width:99px;height:99px;border-radius: 10px;"> \
        </li> ';
            $('.img-list').append(div);
            lrz(files, {
                width: 1200,
                height: 900,
                before: function () {
                    console.log('压缩开始');
                },
                fail: function (err) {
                    console.error(err);
                },
                always: function () {
                    console.log('压缩结束');
                },
                done: function (results) {
                    // 你需要的数据都在这里，可以以字符串的形式传送base64给服务端转存为图片。
                    var data=results.base64;
                    upload(data);
                }
            });
        })
    }
}
//移除上传操作
function removeLi(li,file_id) {
console.log(li)
    upAttachVal('remove', file_id, $('#img_ids'))
    $(li).parent('.waitbox').remove();


}
//图片上传，返回id ,地址
function upload(data) {
    var dataUrl = U('Core/File/uploadPictureBase64');
    $.post(dataUrl, {data: data}, function (msg) {
        if (msg.status == 1) {
            var ids = $('#img_ids').val();
            upAttachVal('add', msg.id, $('#img_ids'));
            //上传成功显示图片

            var div = ' <li class="waitbox">\
                <a class="del-btn am-icon-close"  onclick="removeLi(this, '+msg.id+')" style="position:absolute;right: 5px;top: -5px;color: red"></a>\
            <img src= ' + msg.path + '  style="width:99px;height:99px; border-radius: 10px;"> \
        </li> ';
            $('.loadingBox').hide();
            $('.img-list').append(div);
        } else {
            toast.error(msg.info);
        }
    }, 'json')

}
//拼接图片ID
function upAttachVal(type, attachId, obj) {
    var $attach_ids = obj;
    var attachVal = $attach_ids.val();
    var attachArr = attachVal.split(',');
    var newArr = [];

    for (var i in attachArr) {
        if (attachArr[i] !== '' && attachArr[i] !== attachId.toString()) {
            newArr.push(attachArr[i]);
        }
    }
    type === 'add' && newArr.push(attachId);
    if (newArr.length <= 9) {
        $attach_ids.val(newArr.join(','));
        return newArr;
    } else {
        return false;
    }

}

//单图上传
function add_one_img() {
    var filechooser = document.getElementById("chooseOne");
    $("#upload").on("click", function () {
        filechooser.click();
    })
    filechooser.onchange = function () {
        if (!this.files.length) return;
        var files = Array.prototype.slice.call(this.files);
        if (files.length > 9) {
            alert("最多同时只可上传9张图片");
            return;
        }
        files.forEach(function (files, i) {
            lrz(files, {
                width: 1200,
                height: 900,
                before: function () {
                    console.log('压缩开始');
                },
                fail: function (err) {
                    console.error(err);
                },
                always: function () {
                    console.log('压缩结束');
                },
                done: function (results) {
                    // 你需要的数据都在这里，可以以字符串的形式传送base64给服务端转存为图片。
                    var data=results.base64;
                    upload(data);
                }
            });
        })
    }
    //图片上传，返回id ,地址
    function upload(data) {
        console.log(data);
        var dataUrl = U('Core/File/uploadPictureBase64');
        $.post(dataUrl, {data: data}, function (msg) {
            if (msg.status == 1) {
                console.log(msg);
                //上传成功显示图片
                var ids = $('#one_img_id').val(msg.id);
                if (!msg.id == null) {
                    $('.show_cover').hide();
                } else {
                    $('.show_cover').show();
                }
                $("#cover_url").html('');
                $("#cover_url").html('<img src="' + msg.path + '"style="width:72px;height:72px"  data-role="issue_cover" >');
            } else {
                toast.error(msg.info);
            }
        }, 'json')

    }
}