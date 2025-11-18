# Tutorial 0: Introduction to Agentic AI

**Time: 20 minutes** | **Difficulty: Beginner**

Welcome to the first tutorial in our series on building AI agents! In this tutorial, we'll explore what makes AI systems "agentic" and understand the fundamental patterns that power autonomous agents.

## 🎯 Learning Objectives

By the end of this tutorial, you'll understand:

- The difference between chatbots and AI agents
- What "agentic" behavior means in the context of AI
- The ReAct (Reason-Act-Observe) pattern
- When to use agents vs simple API calls
- How tool use enables agent capabilities

## 📖 What is an AI Agent?

### Chatbot vs Agent

Think of the difference like this:

**🤖 Chatbot** (Traditional LLM Use):

- You ask a question → It responds
- Single turn interaction
- Relies only on its training data
- Cannot take actions or gather new information
- Passive responder

**🧠 AI Agent** (Agentic System):

- You give a goal → It figures out how to achieve it
- Multi-turn autonomous operation
- Can use tools to gather information or take actions
- Makes decisions about next steps
- Active problem solver

### Example Comparison

**Chatbot Interaction:**

```
You: "What's the weather in San Francisco?"
Bot: "I don't have access to real-time weather data.
      I was last trained in [date]..."
```

**Agent Interaction:**

```
You: "What's the weather in San Francisco?"
Agent: [Thinks: I need current weather data]
        [Acts: Calls weather API for San Francisco]
        [Observes: API returns 68°F, sunny]
        [Responds: "It's currently 68°F and sunny in San Francisco"]
```

## 🔄 The ReAct Pattern

ReAct (Reason-Act-Observe) is the fundamental pattern that powers agentic behavior. It's a loop that continues until the task is complete:

```
┌─────────────────────────────────────────┐
│  1. REASON (Think)                      │
│     "What do I need to do next?"        │
│     "What information do I need?"       │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  2. ACT (Execute)                       │
│     "Call a tool to get information"    │
│     "Perform an action"                 │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  3. OBSERVE (Analyze)                   │
│     "What did the tool return?"         │
│     "Do I have enough information?"     │
└──────────────┬──────────────────────────┘
               │
               ▼
        ┌──────────────┐
        │  Complete?   │
        └──────┬───────┘
               │
        ┌──────┴──────┐
        │             │
       No            Yes
        │             │
        │             ▼
        │      ┌──────────────┐
        │      │  Respond to  │
        │      │     User     │
        │      └──────────────┘
        │
        └──────> (Loop back to REASON)
```

### Real Example

**Task**: "Book me a flight to New York tomorrow"

```
Iteration 1:
  REASON:  "I need to know the user's departure city and preferred time"
  ACT:     Ask user for details
  OBSERVE: User provides "San Francisco, morning flight"

Iteration 2:
  REASON:  "Now I can search for flights"
  ACT:     Call flight search API
  OBSERVE: Found 3 morning flights

Iteration 3:
  REASON:  "I should present options and get confirmation"
  ACT:     Show flight options to user
  OBSERVE: User selects 7:30 AM flight

Iteration 4:
  REASON:  "Now I can book the selected flight"
  ACT:     Call booking API
  OBSERVE: Booking confirmed

COMPLETE: "Your flight is booked! Confirmation #ABC123"
```

## 🛠️ How Tool Use Enables Agents

Tools are **functions that Claude can call** to:

1. **Get Information**: Weather, stock prices, database queries
2. **Take Actions**: Send emails, book appointments, make purchases
3. **Compute**: Math calculations, data analysis, code execution
4. **Interact**: Web search, API calls, file operations

### Anatomy of a Tool

A tool definition tells Claude:

```php
[
    'name' => 'get_weather',              // What to call it
    'description' => 'Get current weather  // What it does
                      for a location',
    'input_schema' => [                   // What parameters it needs
        'type' => 'object',
        'properties' => [
            'location' => [
                'type' => 'string',
                'description' => 'City name'
            ]
        ],
        'required' => ['location']
    ]
]
```

### Tool Use Flow

```
1. You provide tools to Claude
   ↓
2. Claude decides if/when to use them
   ↓
3. Claude requests tool execution with parameters
   ↓
4. Your code executes the tool
   ↓
5. You return results to Claude
   ↓
6. Claude uses results to formulate response
```

## 🤔 When to Use Agents

### ✅ Good Use Cases for Agents

- **Research Tasks**: "Find the top 5 ML papers on agent architectures"
- **Multi-step Workflows**: "Analyze this dataset and create a report"
- **Dynamic Problem Solving**: "Debug why the API is returning errors"
- **Information Gathering**: "Compare prices across 3 vendors"
- **Task Automation**: "Summarize my emails and draft responses"

### ❌ When NOT to Use Agents

- **Simple Q&A**: "What is Python?" → Direct response is fine
- **Static Content**: "Translate this text" → No tools needed
- **Real-time Chat**: High latency from multiple iterations
- **Deterministic Tasks**: "Calculate 2+2" → Tool call overhead unnecessary
- **Cost-Sensitive**: Agents use more tokens (more iterations)

### Decision Matrix

| Scenario                 | Simple API Call | Agent |
| ------------------------ | --------------- | ----- |
| User asks for definition | ✅              | ❌    |
| User needs current data  | ❌              | ✅    |
| Multi-step reasoning     | ❌              | ✅    |
| Needs to take action     | ❌              | ✅    |
| Simple calculation       | ✅              | ❌    |
| Complex workflow         | ❌              | ✅    |

## 🎯 Key Concepts

### 1. Autonomy

Agents make decisions about **what** to do and **when** to do it. You don't script every step; you give them capabilities and a goal.

### 2. Tool Use

Tools extend Claude's capabilities beyond its training data. They're the "hands and eyes" of your agent.

### 3. State Management

Agents maintain conversation history across turns. Each iteration builds on previous observations.

### 4. Stop Conditions

Agents need to know when they're done. This could be:

- Task completed successfully
- Maximum iterations reached
- Error encountered
- User explicitly stops

### 5. Iteration Limits

Always set max iterations to prevent infinite loops:

```php
$maxIterations = 10;  // Safety limit
```

## 📊 Costs and Considerations

### Token Usage

Agents use more tokens because:

- Multiple API calls (each iteration)
- Tool definitions in every request
- Growing conversation history
- Special system prompts for tool use

**Example**: A simple question might use 500 tokens, while an agent task could use 5,000+ tokens.

### Latency

Each iteration adds ~1-3 seconds. A 5-iteration agent task takes 5-15 seconds.

### Reliability

More complexity = more failure points:

- Tools can fail
- APIs can timeout
- Agent might get stuck
- Need robust error handling

## 💡 Best Practices

1. **Start Simple**: Begin with one tool, add complexity gradually
2. **Clear Tool Descriptions**: Help Claude choose the right tool
3. **Validate Input**: Check tool parameters before execution
4. **Handle Errors Gracefully**: Tools will fail; plan for it
5. **Limit Iterations**: Prevent runaway loops
6. **Log Everything**: Debug agents by reviewing their reasoning
7. **Test Edge Cases**: What if tools return errors? Empty results?

## 🔍 Debugging Agents

When debugging, look at:

1. **Tool Selection**: Did Claude pick the right tool?
2. **Parameters**: Are the inputs correct?
3. **Tool Results**: What data came back?
4. **Stop Reason**: Why did it stop? (`tool_use`, `end_turn`, `max_tokens`)
5. **Iteration Count**: Did it hit the limit?
6. **Token Usage**: Are you approaching limits?

## 🎓 Types of Agents (Preview)

We'll explore these in later tutorials:

### Simple ReAct Agent (Tutorial 2)

Basic loop with tool calling

### Multi-Tool Agent (Tutorial 3)

Chooses from multiple tools

### Production Agent (Tutorial 4)

Error handling, retries, memory

### Advanced ReAct (Tutorial 5)

Planning, reflection, extended thinking

### Agentic Framework (Tutorial 6)

Task decomposition, parallel execution, orchestration

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] Difference between chatbots and agents
- [ ] What the ReAct pattern is
- [ ] How tools enable agent capabilities
- [ ] When to use agents vs simple API calls
- [ ] Why iteration limits are important

## 🚀 Next Steps

Ready to build your first agent? Continue to:

**[Tutorial 1: Your First Agent →](../01-first-agent/)**

You'll build a working agent with a calculator tool and see the ReAct loop in action!

## 📚 Further Reading

- [ReAct Paper (Yao et al., 2022)](https://arxiv.org/abs/2210.03629)
- [Claude Tool Use Documentation](https://docs.claude.com/en/docs/agents-and-tools/tool-use/overview)
- [SDK Examples: tool_use_overview.php](../../examples/tool_use_overview.php)

## 💻 Try It Yourself

Run the companion code example:

```bash
php tutorials/00-introduction/concepts.php
```

This will demonstrate the concepts with working code examples!
