@extends('Index')

@section('contents')
    @php $lastLevel = 4 @endphp

    @foreach ($members as $member)
        @php
            $highestLevel = count($pageLevels) - 1;
            $normalisedPageLevel = min($member['level'], $highestLevel);
        @endphp

        @for ($level = $highestLevel; $level >= 0; $level--)
            @if ($normalisedPageLevel == min($level, $highestLevel) && $lastLevel >= $level + 1)
                <h2>{{ $pageLevels[$level] }}</h2>
                @break
            @endif
        @endfor

        @php
            $lastLevel = $normalisedPageLevel;
            $frontView = "/minecraft/skin?vr=-10&amp;hr=20&amp;hrh=0&amp;vrla=-20&amp;vrra=20&amp;vrll=15&amp;vrrl=-10&amp;ratio=4&amp;format=png&amp;user={$member['userName']}";
            $backView = "/minecraft/skin?vr=-10&amp;hr=200&amp;hrh=0&amp;vrla=-20&amp;vrra=20&amp;vrll=15&amp;vrrl=-10&amp;ratio=4&amp;format=png&amp;user={$member['userName']}";
            $preloadLinks[] = $backView;
        @endphp

        <div class="spelerswrapper">
            <table>
                <tr>
                    <td class="avatarbox">
                        <img class="mc-speler-avatar" alt="Avatar van {{ $member['realName'] }}" title="Avatar van {{ $member['realName'] }}" src="{!! $frontView !!}" data-vooraanzicht="{!! $frontView !!}" data-achteraanzicht="{!! $backView !!}" />
                    </td>
                    <td class="spelersinfobox">

                        <span class="spelersnaam">{{ $member['userName'] }}</span>

                        @if ($member['donor'] == 1)
                            <br /><span class="donor">Donateur</span>
                        @endif

                        <br />Echte naam: {{ $member['realName'] }}
                        <br />Status: {{ $member['status'] }}

                        @if ($member['level'] >= 3 && $member['level'] <= 5)
                            <br />Niveau: {{ $level[$member['level']] }}
                        @endif

                    </td>
                </tr>
            </table>
        </div>
    @endforeach
@endsection