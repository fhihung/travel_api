<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TourSuggestionController extends Controller
{
    public function getSuggestion(Request $request)
    {
        // Lấy dữ liệu từ request
        $location = $request->input('location');
        $budget = $request->input('budget');
        $total_people = $request->input('total_people');
        $duration = $request->input('duration');
        $type = $request->input('type');
        $current_location = $request->input('current_location');
        $model = $request->input('model', 'openai'); // Giá trị mặc định là 'openai'

        // Tạo prompt chung cho cả hai mô hình
        $prompt = "Hãy gợi ý một lịch trình du lịch chi tiết cho {$total_people} người từ {$current_location} đến {$location} trong {$duration}, với loại hình du lịch {$type} và ngân sách {$budget} cho cả nhóm .
    Vui lòng trả về kết quả dưới dạng JSON với cấu trúc như sau:
    {
      \"location\": \"Tên địa điểm\",
      \"days\": \"Số ngày đi du lịch\",
      \"activities\": [
        {
          \"day\": 1,
          \"schedule\": [
            {
              \"time\": \"8:00 AM\",
              \"activity\": \"Mô tả hoạt động buổi sáng\",
              \"transport\": \"Phương tiện di chuyển\",
              \"cost\": \"Chi phí cho hoạt động\"
            },
            {
              \"time\": \"12:00 PM\",
              \"activity\": \"Mô tả hoạt động buổi trưa\",
              \"restaurant\": {
                \"name\": \"Tên nhà hàng\",
                \"type\": \"Loại món ăn\",
                \"price_range\": \"Khoảng giá\"
              }
            },
            {
              \"time\": \"6:00 PM\",
              \"activity\": \"Mô tả hoạt động buổi tối\",
              \"cost\": \"Chi phí cho hoạt động\"
            }
          ]
        },
        {
          \"day\": 2,
          \"schedule\": [
            {
              \"time\": \"8:00 AM\",
              \"activity\": \"Mô tả hoạt động buổi sáng\",
              \"transport\": \"Phương tiện di chuyển\",
              \"cost\": \"Chi phí cho hoạt động\"
            },
            {
              \"time\": \"12:00 PM\",
              \"activity\": \"Mô tả hoạt động buổi trưa\",
              \"restaurant\": {
                \"name\": \"Tên nhà hàng\",
                \"type\": \"Loại món ăn\",
                \"price_range\": \"Khoảng giá\"
              }
            },
            {
              \"time\": \"6:00 PM\",
              \"activity\": \"Mô tả hoạt động buổi tối\",
              \"cost\": \"Chi phí cho hoạt động\"
            }
          ]
        }
      ],
      \"cost_estimate\": \"Chi phí chính xác/người\",
      \"hotels\": [
        {
          \"name\": \"Tên khách sạn\",
          \"website\": \"Đường link website\",
          \"price_per_night\": \"Giá mỗi đêm\",
          \"rating\": \"Xếp hạng khách sạn\"
        }
      ],
      \"transportation\": {
        \"from_current_location\": \"Phương tiện di chuyển từ {$current_location} đến {$location}\",
        \"within_destination\": \"Phương tiện di chuyển trong suốt chuyến đi\"
      }
    }";


        if ($model === 'openai') {
            // Gọi OpenAI API
            return $this->callOpenAI($prompt);
        } elseif ($model === 'gemini') {
            // Gọi Gemini API (Giả sử API của Gemini tương tự)
            return $this->callGemini($prompt);
        }

        return response()->json(['error' => 'Invalid model selected'], 400);
    }



    private function callOpenAI($prompt)
    {
        set_time_limit(60);
        // Gửi yêu cầu tới OpenAI API với timeout được tăng lên 60 giây
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 1500,
            'temperature' => 0.7,
        ]);
        // Log phản hồi từ API

        // Kiểm tra nếu phản hồi hợp lệ
        if ($response->successful()) {
            return response()->json([
                'suggestion' => json_decode($response->json()['choices'][0]['message']['content'], true),
            ]);
        }

        return response()->json();
    }


    private function callGemini($prompt)
    {
        set_time_limit(60);
        $apiKey = env('GEMINI_API_KEY'); // Lấy API key từ file .env
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ]
        ]);

        // Xử lý dữ liệu trả về từ API
        $responseData = $response->json();

        // Trích xuất dữ liệu JSON từ phần văn bản trong phản hồi
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'];

            // Loại bỏ phần không cần thiết như markdown "```json" và khoảng trắng thừa
            $jsonStart = strpos($rawText, '{');
            $jsonEnd = strrpos($rawText, '}');
            $cleanedJson = substr($rawText, $jsonStart, $jsonEnd - $jsonStart + 1);

            // Chuyển đổi chuỗi JSON về dạng mảng
            $suggestion = json_decode($cleanedJson, true);

            return response()->json(['suggestion' => $suggestion]);
        }

        return response()->json();
    }

}
