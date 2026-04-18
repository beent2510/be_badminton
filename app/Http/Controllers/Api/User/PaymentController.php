<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        return $this->paymentService->search($request->all());
    }

    public function show($id)
    {
        return $this->paymentService->show($id);
    }

    public function store(Request $request)
    {
        return $this->paymentService->store($request->all());
    }

    public function update(Request $request, $id)
    {
        if (!$this->paymentService->show($id)) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        return $this->paymentService->update($id, $request->all());
    }

    public function destroy($id)
    {
        if (!$this->paymentService->show($id)) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        $this->paymentService->destroy($id);
        return response()->json(['message' => 'Payment deleted successfully']);
    }

    public function createZalopayPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'order_code' => 'nullable|string',
        ]);

        $user = $request->user();
        $amount = (float) $request->input('amount');
        $payment = Payment::create([
            'amount' => $amount,
            'payment_method' => 'zalopay',
            'payment_status' => 'pending',
            'user_id' => $user->id,
        ]);

        $config = config('zalopay');
        $appId = (int) ($config['appid'] ?? 0);
        $key1 = (string) ($config['key1'] ?? '');
        $endpoint = rtrim((string) ($config['endpoint'] ?? ''), '/');
        $returnUrl = (string) ($config['return_url'] ?? '');
        $callbackUrl = (string) ($config['callback_url'] ?? '');

        $hasLocalhostUrl = str_contains($returnUrl, 'localhost') || str_contains($returnUrl, '127.0.0.1')
            || str_contains($callbackUrl, 'localhost') || str_contains($callbackUrl, '127.0.0.1');
        if ($hasLocalhostUrl) {
            Log::warning('ZaloPay create payment uses localhost URL. Sandbox callback/redirect may fail outside local machine.', [
                'return_url' => $returnUrl,
                'callback_url' => $callbackUrl,
            ]);
        }

        if (!$appId || !$key1 || !$endpoint || !$returnUrl) {
            return response()->json(['message' => 'Thiếu cấu hình ZaloPay'], 500);
        }

        $endpointCreate = str_contains($endpoint, '/v2/create') ? $endpoint : ($endpoint . '/v2/create');

        $orderCode = $request->input('order_code');
        if (empty($orderCode)) {
            $orderCode = 'PAY' . $payment->id . 'T' . now()->format('His');
        }
        $appTransId = date('ymd') . '_' . $orderCode;

        $items = json_encode([]);
        $embedData = json_encode([
            'redirecturl' => $returnUrl,
            'payment_id' => $payment->id,
        ]);

        $order = [
            'app_id' => $appId,
            'app_time' => round(microtime(true) * 1000),
            'app_trans_id' => $appTransId,
            'app_user' => (string) $user->id,
            'item' => $items,
            'embed_data' => $embedData,
            'amount' => (int) round($amount),
            'description' => 'Thanh toan dat san #' . $payment->id,
            'bank_code' => '',
        ];

        if (!empty($callbackUrl)) {
            $order['callback_url'] = $callbackUrl;
        }

        $macData = $order['app_id'] . '|' . $order['app_trans_id'] . '|' . $order['app_user'] . '|' . $order['amount']
            . '|' . $order['app_time'] . '|' . $order['embed_data'] . '|' . $order['item'];
        $order['mac'] = hash_hmac('sha256', $macData, $key1);

        try {
            $resp = Http::asForm()->post($endpointCreate, $order);
            $result = $resp->json();
            Log::info('ZaloPay create payment response', [
                'http_status' => $resp->status(),
                'return_code' => $result['return_code'] ?? null,
                'return_message' => $result['return_message'] ?? null,
                'sub_return_code' => $result['sub_return_code'] ?? null,
                'sub_return_message' => $result['sub_return_message'] ?? null,
                'app_trans_id' => $appTransId,
                'payment_id' => $payment->id,
            ]);
        } catch (\Throwable $ex) {
            Log::error('ZaloPay create payment exception', ['error' => $ex->getMessage()]);
            return response()->json(['message' => 'Không thể kết nối ZaloPay'], 500);
        }

        if (($result['return_code'] ?? null) == 1 && !empty($result['order_url'])) {
            return response()->json([
                'success' => true,
                'payment_url' => $result['order_url'],
                'payment_id' => $payment->id,
                'app_trans_id' => $appTransId,
            ]);
        }

        $payment->update(['payment_status' => 'failed']);
        return response()->json([
            'success' => false,
            'message' => $result['return_message'] ?? 'Lỗi tạo đơn hàng ZaloPay',
        ], 500);
    }

    public function zaloReturn(Request $request)
    {
        $frontendUrl = trim((string) env('URL_ZALO_FRONTEND', 'http://localhost:5173/zalo_return'));
        $key2 = (string) config('zalopay.key2');

        Log::info('ZaloPay return called', [
            'method' => $request->method(),
            'full_url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        // Browser redirect from ZaloPay
        if ($request->isMethod('get')) {
            $appTransId = (string) $request->query('apptransid', '');
            preg_match('/PAY(\d+)T/', $appTransId, $matches);
            $paymentId = isset($matches[1]) ? (int) $matches[1] : null;
            $status = (string) $request->query('status', '0');
            $isSuccess = $status === '1';

            if ($paymentId) {
                $payment = Payment::find($paymentId);
                if ($payment) {
                    $payment->update([
                        'payment_status' => $isSuccess ? 'paid' : 'failed',
                        'paid_at' => $isSuccess ? now() : null,
                    ]);
                }
            }

            $resultStatus = $isSuccess ? 'success' : 'failed';
            return redirect($frontendUrl . '?status=' . $resultStatus . '&payment_id=' . ($paymentId ?? ''));
        }

        // Server callback verification
        $postdata = file_get_contents('php://input');
        $postdatajson = json_decode($postdata, true);

        if (!is_array($postdatajson) || !isset($postdatajson['data'], $postdatajson['mac'])) {
            Log::warning('ZaloPay callback invalid payload', [
                'payload' => $postdata,
            ]);
            return response()->json(['return_code' => -1, 'return_message' => 'invalid data']);
        }

        $mac = hash_hmac('sha256', $postdatajson['data'], $key2);
        if (strcmp($mac, $postdatajson['mac']) !== 0) {
            Log::warning('ZaloPay callback invalid mac', [
                'app_trans_id' => data_get(json_decode($postdatajson['data'], true), 'app_trans_id'),
            ]);
            return response()->json(['return_code' => -1, 'return_message' => 'invalid mac']);
        }

        $dataJson = json_decode($postdatajson['data'], true);
        $appTransId = (string) ($dataJson['app_trans_id'] ?? '');
        preg_match('/PAY(\d+)T/', $appTransId, $matches);
        $paymentId = isset($matches[1]) ? (int) $matches[1] : null;

        if ($paymentId) {
            $payment = Payment::find($paymentId);
            if ($payment) {
                $isSuccess = ($dataJson['return_code'] ?? 0) == 1;
                $payment->update([
                    'payment_status' => $isSuccess ? 'paid' : 'failed',
                    'paid_at' => $isSuccess ? now() : null,
                ]);
                Log::info('ZaloPay callback payment updated', [
                    'payment_id' => $paymentId,
                    'status' => $isSuccess ? 'paid' : 'failed',
                    'app_trans_id' => $appTransId,
                ]);
            }
        }

        return response()->json(['return_code' => 1, 'return_message' => 'success']);
    }
}
