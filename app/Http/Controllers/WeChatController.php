<?php

namespace App\Http\Controllers;

use App\User;
use EasyWeChat\Foundation\Application;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Storage;

class WeChatController extends Controller
{
    public function serve()
    {
        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    switch ($message['Event']) {
                        case 'subscribe':
                            if (User::bindWeChat($message)) {
                                return "欢迎关注Nerve";
                            } else {
                                return "Nerve服务器出错啦，请稍等一会取关后重新关注！";
                            }
                            break;
                        default:
                            return 'Nerve';
                            break;
                    }
//                    return '收到事件消息';
                    break;
                case 'text':
//                    return '收到文字消息';
                    return 'Nerve';
                    break;
                case 'image':
//                    return '收到图片消息';
                    return 'Nerve';
                    break;
                case 'voice':
//                    return '收到语音消息';
                    return 'Nerve';
                    break;
                case 'video':
//                    return '收到视频消息';
                    return 'Nerve';
                    break;
                case 'location':
//                    return '收到坐标消息';
                    return 'Nerve';
                    break;
                case 'link':
//                    return '收到链接消息';
                    return 'Nerve';
                    break;
                // ... 其它消息
                default:
//                    return '收到其它消息';
                    return 'Nerve';
                    break;
            }
            return "欢迎关注 Nerve";
        });

        return $wechat->server->serve();
    }

    public function getQrCodeUrl(Application $wechat)
    {
        $user = Auth::user();

        $result = $wechat->qrcode->temporary($user->id, 3 * 60);

        $ticket = $result['ticket'];
        $url = $wechat->qrcode->url($ticket);
        $content = file_get_contents($url);
        Storage::put('public/qrcodes/' . $user->id . '.png', $content);
        $url = Storage::url('public/qrcodes/' . $user->id . '.png');
        return response()->json(['data' => $url], 200);
    }
}
