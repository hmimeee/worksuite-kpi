<table cellspacing="0" cellpadding="0" style="background-color: white; table-layout: fixed; font-size: 10pt; font-family: Arial; width: 0px; margin-left: 30%;">
    <colgroup><col width="23" /><col width="91" /><col width="100" /><col width="115" /><col width="87" /><col width="71" /><col width="100" /><col width="24" /></colgroup>
    <tbody>
        <tr style="height: 7px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
        </tr>
        <tr style="height: 41px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle; background-color: #00AEFF;">&nbsp;</td>
            <td rowspan="1" colspan="6" data-sheets-value="{&quot;1&quot;:2,&quot;2&quot;:&quot;Task marked as Complete&quot;}" style="overflow: hidden; padding: 2px 3px; vertical-align: middle; background-color: #00AEFF; font-family: Calibri; font-size: 20pt; font-weight: bold; color: rgb(255, 255, 255); text-align: center;">{{$headmessage}}</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle; background-color: #00AEFF;">&nbsp;</td>
        </tr>
        <tr style="height: 21px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: #00AEFF;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: #00AEFF; font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(255, 255, 255);"><span style="font-size: 11pt; font-family: Calibri, Arial;">ID: </span><span style="font-size: 12pt; font-family: Calibri, Arial;">#{{$infraction->id}}</span></td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: #00AEFF;">&nbsp;</td>
            <td align="right" style=" padding: 2px 3px; vertical-align: bottom; background-color: #00AEFF;"><img style="margin-bottom: -20px; width: 36px;" src="{{url('favicon.png')}}" alt="{{config('app.name')}}"/></td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: #00AEFF;">&nbsp;</td>
            <td style="width: 71px; height: 21px; overflow: hidden; vertical-align: bottom; background-color: #00AEFF;"></td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: #00AEFF; font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(255, 255, 255);">{{date('d M Y')}}</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: #00AEFF;">&nbsp;</td>
        </tr>
        <tr style="height: 21px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
        </tr>
        <tr style="height: 41px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle;">&nbsp;</td>
            <td rowspan="1" colspan="6" style="overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; color: #000; text-align: center;"><span style="font-size: 14pt; font-family: Calibri, Arial;">{{auth()->user()->name}}</span><span style="font-size: 11pt; font-family: Calibri, Arial;"> {{$bodymessage}}</span></td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle;">&nbsp;</td>
        </tr>
        <tr style="height: 35px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle;">&nbsp;</td>
            <td style="border-right: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(0, 177, 231);">Employee:</td>
            <td rowspan="1" colspan="5" style="border-top: 1px solid rgb(239, 239, 239);  border-right: 1px solid rgb(239, 239, 239); border-bottom: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 14pt; font-weight: bold; overflow-wrap: break-word; color: rgb(0, 177, 231);">{{$infraction->user->name}}</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle;">&nbsp;</td>
        </tr>
        <tr style="height: 29px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle;">&nbsp;</td>
            <td style="border-right: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(0, 177, 231);">Type:</td>
            <td rowspan="1" colspan="2" style="border-right: 1px solid rgb(239, 239, 239); border-bottom: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: #000;">{{ucwords($infraction->type->name ?? $infraction->infraction_type)}}</td>
            <td style="border-right: 1px solid rgb(239, 239, 239); border-bottom: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(0, 177, 231);">Reduction Points:</td>
            <td rowspan="1" colspan="2" style="border-right: 1px solid rgb(239, 239, 239); border-bottom: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: #000;">{{$infraction->reduction_points}}</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle;">&nbsp;</td>
        </tr>
        <tr style="height: 29px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle;">&nbsp;</td>
            <td style=" overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(0, 177, 231); border-right: 1px solid rgb(239, 239, 239); ">Creator:</td>
            <td rowspan="1" colspan="2" style="border-right: 1px solid rgb(239, 239, 239); border-bottom: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: #000;">{{$creator}}</td>
            <td style="border-right: 1px solid rgb(239, 239, 239); border-bottom: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(0, 177, 231);">Created Date:</td>
            <td rowspan="1" colspan="2" style="border-right: 1px solid rgb(239, 239, 239); border-bottom: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: #000;">{{$infraction->created_at->format('d-m-Y')}}</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: middle;">&nbsp;</td>
        </tr>
        <tr style="height: 12px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: top;">&nbsp;</td>
            <td style="border-right: 1px solid rgb(239, 239, 239);  overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(0, 177, 231);"></td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: top;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: top;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: top;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: top;">&nbsp;</td>
            <td style="border-right: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: top;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: top;">&nbsp;</td>
        </tr>
        <tr style="height: 35px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: top;">&nbsp;</td>
            <td style="border-right: 1px solid rgb(239, 239, 239);  overflow: hidden; padding: 2px 3px; vertical-align: middle; font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(0, 177, 231);">Notes:</td>
            <td rowspan="2" colspan="5" style="border-left: 1px solid rgb(239, 239, 239);border-right: 1px solid rgb(239, 239, 239); border-bottom: 1px solid rgb(239, 239, 239); overflow: hidden; padding: 2px 3px; vertical-align: top; font-family: Calibri; font-size: 11pt; overflow-wrap: break-word; color: rgb(66, 133, 244);">
            <div style="max-height: 175px; font-family: Calibri;">
                {!! $infraction->details !!}
                @if($infraction->type)
                <br>
                {!! $infraction->type->details ?? null !!}
                @endif
            </div>
            </td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: top;">&nbsp;</td>
        </tr>
        <tr style="height: 21px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
        </tr>
        <tr style="height: 21px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
        </tr>

        @if(!$deleted)
        <tr style="height: 50px;">
            <td colspan="8" align="center">
                <a href="{{$url}}?view-infraction={{$infraction->id}}" style="box-sizing: border-box; color: rgb(255, 255, 255); background-color: #00AEFF; border-image: initial; border-width: 10px 20px; border-style: solid; border-color: #00AEFF; border-radius: 50px; text-decoration-line: none;padding: 5px;font-weight: bold;">View Infraction</a>
            </td>
        </tr>
        @endif
        
        <tr style="height: 21px;">
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
            <td style="overflow: hidden; padding: 2px 3px; vertical-align: bottom;">&nbsp;</td>
        </tr>
        <tr style="height: 26px;">
            <td style="font-size: 13.3333px; overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="font-size: 13.3333px; overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="font-size: 13.3333px; overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td rowspan="1" colspan="2" style="overflow: hidden; padding: 2px 3px; vertical-align: middle; background-color: rgb(183, 183, 183); font-family: Calibri; font-size: 11pt; font-weight: bold; color: rgb(255, 255, 255); text-align: center;">office.seoviserx.com</td>
            <td style="font-size: 13.3333px; overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="font-size: 13.3333px; overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
            <td style="font-size: 13.3333px; overflow: hidden; padding: 2px 3px; vertical-align: bottom; background-color: rgb(183, 183, 183);">&nbsp;</td>
        </tr>
    </tbody>
</table>
