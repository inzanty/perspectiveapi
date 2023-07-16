<?php

namespace PerspectiveApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class CommentsClient
{
	const API_URL = 'https://commentanalyzer.googleapis.com/v1alpha1';

	protected string $token;
	protected array $comment;
	protected array $languages;
	protected array $context;
	protected array $requestedAttributes;
	protected bool $spanAnnotations;
	protected bool $doNotStore;
	protected string $clientToken;
	protected string $sessionId;
	protected array $attributeScores;
	protected string $communityId;

	/**
	 * @param string $token
	 */
	public function __construct(string $token)
	{
		$this->token = $token;
	}

	/**
	 * Make an Analyze Comment request
	 *
	 * @return CommentsResponse
	 * @throws CommentsException|GuzzleException
	 */
	public function analyze(): CommentsResponse
	{
		$fields = [
			'comment', 'languages', 'requestedAttributes', 'context', 'spanAnnotations', 'doNotStore', 'clientToken',
			'sessionId'
		];

		return $this->request('analyze', $fields);
	}

	/**
	 * Sending feedback: SuggestCommentScore
	 *
	 * @return CommentsResponse
	 * @throws CommentsException|GuzzleException
	 */
	public function suggestScore(): CommentsResponse
	{
		$fields = ['comment', 'context', 'attributeScores', 'languages', 'communityId', 'clientToken'];

		return $this->request('suggestscore', $fields);
	}

	/**
	 * The text to score. This is assumed to be utf8 raw text of the text to be checked.
	 *
	 * @param array $comment
	 *
	 * @example ['text' => string, 'type' => string]
	 */
	public function comment(array $comment): void
	{
		$this->comment = $comment;
	}

	/**
	 * A list of objects providing the context for comment
	 *
	 * @param array $context
	 *
	 * @example ['entries': [{'text': string, 'type': string}]
	 */
	public function context(array $context): void
	{
		$this->context = $context;
	}

	/**
	 * A list of ISO 631-1 two-letter language codes specifying the language(s) that comment is in
	 *
	 * @param array $languages
	 *
	 * @example [string]
	 */
	public function languages(array $languages): void
	{
		$this->languages = $languages;
	}

	/**
	 * A map from model's attribute name to a configuration object
	 *
	 * @param array $requestedAttributes
	 *
	 * @example [string: {'scoreType': string, 'scoreThreshold': float},]
	 */
	public function requestedAttributes(array $requestedAttributes): void
	{
		$this->requestedAttributes = $requestedAttributes;
	}

	/**
	 * A boolean value that indicates if the request should return spans that describe the scores for each part of the
	 * text
	 *
	 * @param bool $spanAnnotations
	 *
	 * @example bool
	 */
	public function spanAnnotations(bool $spanAnnotations): void
	{
		$this->spanAnnotations = $spanAnnotations;
	}

	/**
	 * Whether the API is permitted to store comment and context from this request
	 *
	 * @param bool $doNotStore
	 *
	 * @example bool
	 */
	public function doNotStore(bool $doNotStore): void
	{
		$this->doNotStore = $doNotStore;
	}

	/**
	 * An opaque token that is echoed back in the response
	 *
	 * @param string $clientToken
	 *
	 * @example string
	 */
	public function clientToken(string $clientToken)
	{
		$this->clientToken = $clientToken;
	}

	/**
	 * An opaque session id
	 *
	 * @param string $sessionId
	 *
	 * @example string
	 */
	public function sessionId(string $sessionId)
	{
		$this->sessionId = $sessionId;
	}

	/**
	 * A map from model attribute name to per-attribute score objects
	 *
	 * @param array $attributeScores
	 *
	 * @example [string: {
	 *          'summaryScore': {'value': float,'type': string},
	 *          'spanScores': [{'begin': int,'end': int,'score': {'value': float,'type': string}}]
	 *          }]
	 */
	public function attributeScores(array $attributeScores)
	{
		$this->attributeScores = $attributeScores;
	}

	/**
	 * Opaque identifier associating this score suggestion with a particular community
	 *
	 * @param string $communityId
	 *
	 * @example string
	 */
	public function communityId(string $communityId)
	{
		$this->communityId = $communityId;
	}

	/**
	 * Send request to API
	 *
	 * @param string $method
	 * @param array  $fields
	 *
	 * @return CommentsResponse
	 * @throws CommentsException|GuzzleException
	 */
	protected function request(string $method, array $fields): CommentsResponse
	{
		$data = [];
		$client = new Client([
			'defaults' => [
				'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json']
			]
		]);

		foreach ($fields as $field)
		{
			if (isset($this->{$field}))
			{
				$data[$field] = $this->{$field};
			}
		}

		try
		{
			$response = $client->post(self::API_URL . "/comments:{$method}?key={$this->token}", ['json' => $data]);
		}
		catch (ClientException|GuzzleException $e)
		{
			$error = json_decode($e->getResponse()->getBody(), true);

			if (isset($error['error']))
			{
				throw new CommentsException($error['error']['message'], $error['error']['code']);
			}
			else
			{
				throw $e;
			}
		}

		$result = json_decode($response->getBody(), true);

		return new CommentsResponse($result);
	}
}
