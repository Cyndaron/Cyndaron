@extends('Index')

@section('contents')
    <table class="ledenlijst">
        @foreach ($members as $member)
            @php $avatar = $member->avatar ? '/' . \Cyndaron\User\User::AVATAR_DIR . "/{$member->avatar}" : $fallbackImage @endphp
            <tr>
                <td>
                    <img style="height: 150px;" alt="" src="{{ $avatar }}"/>
                </td>
                <td>
                    <b>
                        <span style="text-decoration: underline;">
                            @if ($member->firstName || $member->lastName)
                                {{ $member->getFullName() }}
                            @else
                                {{ $member->username }}
                            @endif
                        </span>
                    </b>
                    <br /><br />
                    {{ $member->role }}
                    
                    @if ($member->comments)
                        <br>
                        {{ $member->comments }}
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
@endsection