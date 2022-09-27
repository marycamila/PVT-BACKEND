<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Contributions</title>
    <link rel="stylesheet" href="{{ public_path('/css/report-print.min.css') }}" media="all" />
</head>

<body>
    <div>
        @include('partials.header', $header)
    </div>

    <p>
        El suscrito Encargado de Cuentas Individuales en base a una revisión de la Base de Datos del Sistema Informático
        de la MUSERPOL de aportes realizados, de:
    </p>

    <div>
        @include('affiliate.police_info')
    </div>
    <strong> CERTIFICA </strong>

    <div class="block">
        <table class="table-info w-100 text-center">
            <thead>
                <tr class="bg-grey-darker text-xxs text-white">
                    <th class="w-10">AÑO</th>
                    <th class="w-15">MES</th>
                    <th class="w-15">TITULAR/VIUDA</th>
                    <th class="w-15" colspan="2">MODALIDAD DE PAGO</th>
                    <th class="w-15">TOTAL COTIZABLE</td>
                    <th class="w-15">APORTE</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($contributions as $contribution)
                    <tr>
                        <td class="data-row py-2">{{ $contribution['year'] }}</td>
                        <td class="data-row py-2">{{ $contribution['month'] }}</td>
                        <td class="data-row py-2">{{ $contribution['rent_class'] }}</td>
                        <td class="data-row py-2" colspan="2">{{ $contribution['description'] }}</td>
                        <td class="data-row py-2">{{ $contribution['quotable'] }}</td>
                        <td class="data-row py-2">{{ $contribution['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>
        <table class="table-info w-100 text-center">
            <thead>
                <tr class="bg-grey-darker text-xxs text-white">
                    <th class="w-10">NOTA: Toda vez que, la presente certificación detalla información referencial
                        respecto a los aportes para el beneficio del Auxulio Mortuorio, se requiere al solicitante
                        efectuar la verificación correspondiente de los datos, a fin de no existir reclamos
                        posteriores.
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Asímismo, se efectuó la revisión de datos contenidos en el Sistema Institucional y base de
                        datos antecedentes respecto a los aportes efectuados para el beneficio.
                        En cuanto se certifica para fines consiguientes.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</body>

<div>
    @include('partials.footer_app', $header)
</div>

</html>
