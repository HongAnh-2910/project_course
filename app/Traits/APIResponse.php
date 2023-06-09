<?php
    namespace App\Traits;
    use Illuminate\Http\Response;

    trait APIResponse
    {
        /**
         * @param $data
         * @param $message
         * @param $status
         * @param $code
         * @return \Illuminate\Http\JsonResponse
         */
        public function successResponse($data = null, $message = '', $status = 'Success', $code = Response::HTTP_OK)
        {
            $response = [
                'code'   => 200,
                'status' => $status
            ];
            if ( ! empty($message)) {
                $response['message'] = $message;
            }
            if ($data) {
                $response['data'] = $data;
            }
            return response()->json($response, $code);
        }

        /**
         * @param $message
         * @param $code
         * @return \Illuminate\Http\JsonResponse
         */

        public function errorResponse($message = '' , $code)
        {
            $response = [
                'code'   => 400,
                'message' => $message
            ];

            return response()->json($response , $code);
        }
    }
