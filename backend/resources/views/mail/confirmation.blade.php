<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Confirmation</title>
</head>
<body>
    <h2>{{$appointment->customer->name}}, your appointment is confirmed!</h2>

    <p><strong>Date:</strong>
        {{ $appointment->start_datetime->format('d M Y') }}
    </p>

    <p><strong>Time:</strong>
        {{ $appointment->start_datetime->format('H:i') }}
        –
        {{ $appointment->end_datetime->format('H:i') }}
    </p>

    <p><strong>Service:</strong> {{ $appointment->service->name }}</p>
    <p><strong>Barber:</strong> {{ $appointment->employee->user->name }}</p>

    <p>Thank you for your booking!</p>
</body>
</html>
