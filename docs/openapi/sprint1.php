<?php
use OpenApi\Annotations as OA;

/**
 * @OA\PathItem(
 *     path="/api/v10/payment",
 *     @OA\Post(
 *         operationId="postPaymentOverview",
 *         summary="Retrieve parent payment overview",
 *         tags={"Payments"},
 *         @OA\RequestBody(
 *             required=false,
 *             @OA\MediaType(
 *                 mediaType="application/json",
 *                 @OA\Schema(
 *                     type="object",
 *                     @OA\Property(
 *                         property="studentitems",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             required={"studentID", "amount"},
 *                             @OA\Property(property="studentID", type="integer", example=101),
 *                             @OA\Property(property="amount", type="number", format="float", example=2000)
 *                         )
 *                     )
 *                 )
 *             )
 *         ),
 *         @OA\Response(response=200, description="Success")
 *     )
 * )
 *
 * @OA\PathItem(
 *     path="/api/v10/payment/save_payment",
 *     @OA\Post(
 *         operationId="postPaymentSavePayment",
 *         summary="M-PESA STK Push",
 *         tags={"Payments"},
 *         @OA\RequestBody(
 *             required=true,
 *             @OA\MediaType(
 *                 mediaType="application/json",
 *                 @OA\Schema(
 *                     type="object",
 *                     required={"phonenumber", "studentitems"},
 *                     @OA\Property(
 *                         property="phonenumber",
 *                         description="Safaricom phone number",
 *                         type="string",
 *                         example="0720000000"
 *                     ),
 *                     @OA\Property(
 *                         property="studentitems",
 *                         description="List of student payment instructions in KSh",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             required={"studentID", "amount"},
 *                             @OA\Property(property="studentID", type="integer", example=101),
 *                             @OA\Property(property="amount", type="number", format="float", example=2000)
 *                         )
 *                     )
 *                 )
 *             )
 *         ),
 *         @OA\Response(response=200, description="Success"),
 *         @OA\Response(response=400, description="Invalid phonenumber or student item"),
 *         @OA\Response(response=501, description="The POST method is not found")
 *     )
 * )
 */
class Sprint1PaymentPaths {}

/**
 * @OA\PathItem(
 *     path="/quickbooks/export_skeleton",
 *     @OA\Post(
 *         operationId="postQuickbooksExportSkeleton",
 *         summary="Queue a QuickBooks export engine",
 *         tags={"QuickBooks"},
 *         @OA\RequestBody(
 *             required=true,
 *             @OA\MediaType(
 *                 mediaType="application/json",
 *                 @OA\Schema(
 *                     type="object",
 *                     required={"engine"},
 *                     @OA\Property(
 *                         property="engine",
 *                         description="Engine to run",
 *                         type="string",
 *                         enum={"dry-run", "schedule-term", "schedule-month", "schedule-day", "reconciliation"},
 *                         example="dry-run"
 *                     ),
 *                     @OA\Property(property="term", type="string", example="2024 Term 1"),
 *                     @OA\Property(property="month", type="string", format="date", example="2024-09"),
 *                     @OA\Property(property="day", type="string", format="date", example="2024-09-30"),
 *                     @OA\Property(property="start_date", type="string", format="date", example="2024-09-01"),
 *                     @OA\Property(property="end_date", type="string", format="date", example="2024-09-30"),
 *                     @OA\Property(property="notes", type="string", example="Dry-run ahead of FY close"),
 *                     @OA\Property(property="idempotency_key", type="string", example="custom-key-123")
 *                 )
 *             )
 *         ),
 *         @OA\Response(
 *             response=200,
 *             description="Queued",
 *             @OA\JsonContent(
 *                 type="object",
 *                 @OA\Property(property="status", type="string", example="ok"),
 *                 @OA\Property(
 *                     property="data",
 *                     type="object",
 *                     @OA\Property(property="idempotencyKeyID", type="integer", example=15),
 *                     @OA\Property(property="state", type="string", example="complete"),
 *                     @OA\Property(property="replayed", type="boolean", example=false),
 *                     @OA\Property(property="response", type="object")
 *                 )
 *             )
 *         ),
 *         @OA\Response(response=400, description="Invalid engine or filters"),
 *         @OA\Response(response=409, description="Payload hash mismatch for idempotency key")
 *     )
 * )
 */
class QuickbooksExportPaths {}
