<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TourSuggestionController extends Controller
{
    public function getSuggestion(Request $request)
    {
        // Lấy dữ liệu từ request
        $location = $request->input('location');
        $numPeople = $request->input('numPeople');
        $duration = $request->input('duration');
        $type = $request->input('type');
        $model = $request->input('model', 'openai'); // Giá trị mặc định là 'openai'

        // Tạo prompt chung cho cả hai mô hình
        $prompt = "Hãy gợi ý một lịch trình du lịch cho {$numPeople} người đến {$location} trong {$duration}, với loại hình du lịch {$type}. Vui lòng trả về kết quả dưới dạng JSON với cấu trúc như sau:
    {
      \"location\": \"Tên địa điểm\",
      \"days\": \"Số ngày đi du lịch\",
      \"activities\": [
        {
          \"day\": 1,
          \"schedule\": [
            {
              \"time\": \"8:00 AM\",
              \"activity\": \"Mô tả hoạt động buổi sáng\"
            },
            {
              \"time\": \"12:00 PM\",
              \"activity\": \"Mô tả hoạt động buổi trưa\"
            },
            {
              \"time\": \"6:00 PM\",
              \"activity\": \"Mô tả hoạt động buổi tối\"
            }
          ]
        },
        {
          \"day\": 2,
          \"schedule\": [
            {
              \"time\": \"8:00 AM\",
              \"activity\": \"Mô tả hoạt động buổi sáng\"
            },
            {
              \"time\": \"12:00 PM\",
              \"activity\": \"Mô tả hoạt động buổi trưa\"
            },
            {
              \"time\": \"6:00 PM\",
              \"activity\": \"Mô tả hoạt động buổi tối\"
            }
          ]
        }
      ],
      \"costEstimate\": \"Ước tính chi phí\",
      \"hotels\": [
        {
          \"hotelName\": \"Tên khách sạn\",
          \"website\": \"Đường link website\"
        }
      ]
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
            'max_tokens' => 500,
            'temperature' => 0.7,
        ]);

        // Kiểm tra nếu phản hồi hợp lệ
        if ($response->successful()) {
            return response()->json([
                'suggestion' => json_decode($response->json()['choices'][0]['message']['content'], true),
            ]);
        }

        return response()->json(['error' => 'Unable to get suggestion from OpenAI'], 500);
    }


    private function callGemini($prompt)
    {
        set_time_limit(60);
        $apiKey = env('GEMINI_API_KEY'); // Lấy API key từ file .env
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey;

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

        return response()->json(['error' => 'Could not parse response'], 500);
    }

}
