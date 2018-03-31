<?php

namespace App\Http\Controllers;

use App\WarningConfig;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use Auth;

class WarningConfigController extends Controller
{
    public function updateWarningConfig(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:warning_configs,id',
            'value' => 'required|integer',
            'switch' => ['required', Rule::in([true, false])],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return response()->json(['errors' => $errors], 422);
        }

        $data = $request->all();
        $warningConfigs = $user->warningConfigs;
        $warningConfig = $warningConfigs->first(function ($warningConfig) use ($data) {
            return $warningConfig->id === $data['id'];
        });

        if (!$warningConfig) {
            return response()->json(['errors' => 'Not found'], 404);
        }

        $warningConfig->value = $data['value'];
        $warningConfig->switch = $data['switch'];
        if (!$warningConfig->save()) {
            return response()->json(['errors' => 'The server has a problem']);
        }

        return response()->json(null, 204);
    }

    public function deleteWarningConfig($warningConfigId)
    {
        $user = Auth::user();

        $validator = Validator::make([
                'warning_config_id' => $warningConfigId
            ], [
                'warning_config_id' => 'required|exists:warning_configs,id'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return response()->json(['errors' => $errors], 422);
        }

        $warningConfigs = $user->warningConfigs;
        $warningConfig = $warningConfigs->first(function ($warningConfig) use ($warningConfigId) {
            return $warningConfigId == $warningConfig->id;
        });

        if (!$warningConfig) {
            return response()->json(['Not found'], 404);
        }

        $warningConfig->status = 0;
        if (!$warningConfig->save()) {
            return response()->json(['errors' => 'The server has a problem'], 500);
        }

        return response()->json(null, 204);
    }
}
