# AI Configuration

## Providers

Configured in `config/ai.php` and `.env`:

| Provider | Env | Notes |
|----------|-----|--------|
| `null` | `AI_PROVIDER=null` | Deterministic mock; no API key. Default for demos/tests. |
| `openai` | `OPENAI_API_KEY`, `OPENAI_MODEL` | Chat Completions compatible |
| `gemini` | `GEMINI_API_KEY`, `GEMINI_MODEL` | Google Generative Language API |
| `ollama` | `OLLAMA_BASE_URL`, `OLLAMA_MODEL` | Local / self-hosted |

Shared knobs: `AI_TEMPERATURE`, `AI_MAX_TOKENS`, `AI_BUDGET_MONTHLY`, `AI_PROMPT_VERSION`.

Runtime defaults can also be stored in the `ai_settings` table (Admin → AI Settings). Demo seed activates provider `null` / model `null-mock`.

## Abstraction

```
AIProvider (contract)
├── OpenAIProvider
├── GeminiProvider
├── OllamaProvider
└── NullAIProvider
```

`AIManager` resolves the active provider. Application services must not call vendor SDKs directly.

## Response contract

Structured assessment JSON must include:

- `score`, `max_score`
- `criteria[]` with `name`, `score`, `max_score` (+ optional evidence/reasoning/feedback)
- `overall_feedback`
- `confidence` (0–1)

Validated by `App\Services\AI\AIResponseValidator`.

## Prompt templates

Table `prompt_templates` (system seeds):

- `document_assessment` — document / essay reports against rubric
- `essay_answer_assessment` — exam essay answers

Placeholders such as `{{rubric}}`, `{{document_text}}`, `{{answer_text}}` are filled by the assessment services.

## Safety principles

- Prefer evidence quoted from student text; avoid invented citations.
- Lecturer review/finalize is mandatory for official grades.
- Log usage in `ai_usage`; keep audit trail on score changes.
