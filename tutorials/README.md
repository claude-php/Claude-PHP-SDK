# Building Agentic AI with Claude PHP SDK

A comprehensive, progressive tutorial series that teaches you how to build intelligent AI agents from the ground up, culminating in advanced ReAct patterns with planning, reflection, and tool composition.

## 🎯 What You'll Learn

By the end of this series, you'll understand:

- **Core Concepts**: What AI agents are and how they differ from chatbots
- **ReAct Pattern**: The Reason-Act-Observe loop that powers autonomous agents
- **Tool Use**: How to give Claude capabilities through function calling
- **Production Patterns**: Error handling, retries, memory, and state management
- **Advanced Techniques**: Planning, reflection, and multi-step reasoning
- **Agent Architectures**: Building complete orchestration systems

## 👥 Who This Is For

This series is designed for **PHP developers new to AI agents**. We'll explain AI concepts as we go, so prior experience with LLMs is helpful but not required. You should be comfortable with:

- PHP 8.1+ syntax
- Basic HTTP/API concepts
- JSON structures
- Async patterns (helpful but not required)

## 📋 Prerequisites

Before starting, make sure you have:

1. **PHP 8.1 or higher** installed
2. **Composer** for dependency management
3. **Anthropic API Key** ([Get one here](https://console.anthropic.com/))
4. **Claude PHP SDK** installed:
   ```bash
   composer require claude-php/claude-php-sdk
   ```

5. **Environment Setup**:
   Create a `.env` file in the project root:
   ```
   ANTHROPIC_API_KEY=your-api-key-here
   ```

## 🚀 Tutorial Series

### [Tutorial 0: Introduction to Agentic AI](./00-introduction/)
**Time: 20 minutes** | **Difficulty: Beginner**

Understand the fundamental concepts of AI agents, autonomy, and the ReAct pattern.

**What You'll Learn:**
- Agents vs chatbots
- What makes an agent "agentic"
- The ReAct (Reason-Act-Observe) pattern
- When to use agents vs simple API calls

**Files:**
- 📖 [README.md](./00-introduction/README.md) - Concepts guide
- 💻 [concepts.php](./00-introduction/concepts.php) - Interactive examples

---

### [Tutorial 1: Your First Agent](./01-first-agent/)
**Time: 30 minutes** | **Difficulty: Beginner**

Build your first working agent with a single tool (calculator).

**What You'll Learn:**
- Tool definitions and input schemas
- Request → Tool call → Execute → Response flow
- Handling tool results
- Basic conversation continuity

**Files:**
- 📖 [README.md](./01-first-agent/README.md) - Step-by-step guide
- 💻 [simple_agent.php](./01-first-agent/simple_agent.php) - Working code

---

### [Tutorial 2: ReAct Basics](./02-react-basics/)
**Time: 45 minutes** | **Difficulty: Intermediate**

Implement a ReAct loop that enables iterative reasoning and action.

**What You'll Learn:**
- The Reason → Act → Observe loop
- Stop conditions and loop control
- Multi-turn conversations with state
- Debugging agent reasoning

**Files:**
- 📖 [README.md](./02-react-basics/README.md) - ReAct pattern explained
- 💻 [react_agent.php](./02-react-basics/react_agent.php) - ReAct implementation

---

### [Tutorial 3: Multi-Tool Agent](./03-multi-tool-agent/)
**Time: 45 minutes** | **Difficulty: Intermediate**

Expand your agent with multiple tools and intelligent tool selection.

**What You'll Learn:**
- Defining multiple diverse tools
- How Claude selects the right tool
- Parameter extraction and validation
- Tool result formatting
- Debugging tool selection

**Files:**
- 📖 [README.md](./03-multi-tool-agent/README.md) - Multi-tool patterns
- 💻 [multi_tool_agent.php](./03-multi-tool-agent/multi_tool_agent.php) - Agent with 4 tools

---

### [Tutorial 4: Production-Ready Agent](./04-production-ready/)
**Time: 60 minutes** | **Difficulty: Intermediate**

Build a robust, production-ready agent with proper error handling and memory.

**What You'll Learn:**
- Comprehensive error handling
- Retry logic with exponential backoff
- Tool execution error reporting
- Persistent memory with the Memory Tool
- Graceful degradation
- Logging and monitoring

**Files:**
- 📖 [README.md](./04-production-ready/README.md) - Production patterns
- 💻 [production_agent.php](./04-production-ready/production_agent.php) - Robust implementation

---

### [Tutorial 5: Advanced ReAct](./05-advanced-react/)
**Time: 60 minutes** | **Difficulty: Advanced**

Enhance your agent with planning, reflection, and extended thinking.

**What You'll Learn:**
- Plan → Execute → Reflect → Adjust pattern
- Extended thinking for complex reasoning
- Self-correction and adaptation
- Multi-step task decomposition
- Reasoning transparency

**Files:**
- 📖 [README.md](./05-advanced-react/README.md) - Advanced patterns
- 💻 [advanced_react_agent.php](./05-advanced-react/advanced_react_agent.php) - Planning & reflection

---

### [Tutorial 6: Full Agentic Framework](./06-agentic-framework/)
**Time: 90 minutes** | **Difficulty: Advanced**

Build a complete orchestration system with task decomposition and parallel execution.

**What You'll Learn:**
- Agent architecture patterns
- Task decomposition strategies
- Parallel tool execution
- State management at scale
- Tool composition
- Complex workflow orchestration

**Files:**
- 📖 [README.md](./06-agentic-framework/README.md) - Framework design
- 💻 [agentic_framework.php](./06-agentic-framework/agentic_framework.php) - Complete system

---

## 🚀 Advanced Patterns (Tutorials 7-14)

Beyond the foundational patterns, explore advanced agentic AI techniques:

### [Tutorial 7: Chain of Thought (CoT)](./07-chain-of-thought/)
**Time: 45 minutes** | **Difficulty: Intermediate**

Master step-by-step reasoning without tools using the Chain of Thought pattern.

**What You'll Learn:**
- Zero-shot and few-shot CoT prompting
- When to use CoT vs ReAct
- Transparent reasoning processes
- CoT for math, logic, and decision making

**Files:**
- 📖 [README.md](./07-chain-of-thought/README.md)
- 💻 [cot_agent.php](./07-chain-of-thought/cot_agent.php)

---

### [Tutorial 8: Tree of Thoughts (ToT)](./08-tree-of-thoughts/)
**Time: 60 minutes** | **Difficulty: Advanced**

Explore multiple reasoning paths simultaneously with Tree of Thoughts.

**What You'll Learn:**
- Multi-path exploration strategies
- Evaluation and backtracking
- BFS vs DFS vs best-first search
- Solving puzzles and optimization problems

**Files:**
- 📖 [README.md](./08-tree-of-thoughts/README.md)
- 💻 [tot_agent.php](./08-tree-of-thoughts/tot_agent.php)

---

### [Tutorial 9: Plan-and-Execute](./09-plan-and-execute/)
**Time: 45 minutes** | **Difficulty: Intermediate**

Separate planning from execution for more efficient agents.

**What You'll Learn:**
- Explicit planning phases
- Systematic execution
- Plan revision and monitoring
- When to use vs ReAct

**Files:**
- 📖 [README.md](./09-plan-and-execute/README.md)
- 💻 [plan_execute_agent.php](./09-plan-and-execute/plan_execute_agent.php)

---

### [Tutorial 10: Reflection & Self-Critique](./10-reflection/)
**Time: 45 minutes** | **Difficulty: Intermediate**

Build agents that evaluate and improve their own work.

**What You'll Learn:**
- Generate-Reflect-Refine loops
- Quality assessment criteria
- Iterative improvement
- Self-correction techniques

**Files:**
- 📖 [README.md](./10-reflection/README.md)
- 💻 [reflection_agent.php](./10-reflection/reflection_agent.php)

---

### [Tutorial 11: Hierarchical Agents](./11-hierarchical-agents/)
**Time: 60 minutes** | **Difficulty: Advanced**

Organize agents into master-worker hierarchies for complex tasks.

**What You'll Learn:**
- Master-worker architectures
- Task delegation strategies
- Specialized sub-agents
- Result aggregation

**Files:**
- 📖 [README.md](./11-hierarchical-agents/README.md)
- 💻 [hierarchical_agent.php](./11-hierarchical-agents/hierarchical_agent.php)

---

### [Tutorial 12: Multi-Agent Debate](./12-multi-agent-debate/)
**Time: 60 minutes** | **Difficulty: Advanced**

Use multiple agents with different perspectives to reach better decisions.

**What You'll Learn:**
- Debate protocols and rounds
- Role-based prompting (proposer, critic, judge)
- Consensus building
- When debate improves outcomes

**Files:**
- 📖 [README.md](./12-multi-agent-debate/README.md)
- 💻 [debate_agent.php](./12-multi-agent-debate/debate_agent.php)

---

### [Tutorial 13: RAG Pattern](./13-rag-pattern/)
**Time: 60 minutes** | **Difficulty: Advanced**

Integrate document retrieval with generation for knowledge-grounded responses.

**What You'll Learn:**
- Retrieval-Augmented Generation
- Document retrieval strategies
- Context injection techniques
- Citation tracking

**Files:**
- 📖 [README.md](./13-rag-pattern/README.md)
- 💻 [rag_agent.php](./13-rag-pattern/rag_agent.php)

---

### [Tutorial 14: Autonomous Agents](./14-autonomous-agents/)
**Time: 90 minutes** | **Difficulty: Advanced**

Build self-directed agents that pursue goals independently across sessions.

**What You'll Learn:**
- Goal-directed behavior
- State persistence between sessions
- Progress monitoring
- Safety and termination conditions

**Files:**
- 📖 [README.md](./14-autonomous-agents/README.md)
- 💻 [autonomous_agent.php](./14-autonomous-agents/autonomous_agent.php)

---

### [Tutorial 15: Context Management & Advanced Tool Use](./15-context-management/)
**Time: 60 minutes** | **Difficulty: Advanced**

Master context window management with auto-compaction, effort levels, and advanced tool discovery.

**What You'll Learn:**
- Auto-compaction for managing context window size
- Effort levels for controlling response quality
- Tool search for dynamic tool discovery
- MCP toolset configuration
- Computer use v5 features

**Files:**
- 📖 [README.md](./15-context-management/README.md)
- 💻 [context_agent.php](./15-context-management/context_agent.php)

---

### [Tutorial 16: v0.5.2 New Features](./16-v052-features/)
**Time: 60 minutes** | **Difficulty: Intermediate**

Learn about the powerful new features in v0.5.2 that achieve parity with Python SDK v0.76.0.

**What You'll Learn:**
- Server-side tools (executed by Claude's API)
- Authentication flexibility (OAuth2, Bearer tokens, proxies)
- Enhanced stream management with automatic cleanup
- Binary request streaming capabilities
- How to mix client-side and server-side tools

**Files:**
- 📖 [README.md](./16-v052-features/README.md)
- 💻 [v052_features.php](./16-v052-features/v052_features.php)

---

### [Tutorial 17: v0.6.0 New Features](./17-v060-features/)
**Time: 60 minutes** | **Difficulty: Intermediate**

Learn about the features added in v0.6.0 for full parity with Python SDK v0.80.0, including the new Claude 4.6 models, adaptive thinking, fast-mode inference, and the complete server-side tool suite.

**What You'll Learn:**
- Adaptive thinking (`type: "adaptive"`) — model decides how much to think
- Speed / fast-mode parameter for high-throughput Beta Messages
- `output_config` for structured outputs in GA and Beta Messages
- Typed `ModelParam` constants for all current Claude models
- Code execution tool (GA + Beta REPL-state persistence)
- Memory tool for file-based cross-session persistence
- Web fetch tool with domain restrictions and token caps
- Beta web search v2 (`web_search_20260209`) with `allowed_callers`

**Files:**
- 📖 [README.md](./17-v060-features/README.md)
- 💻 [v060_features.php](./17-v060-features/v060_features.php)

---

## 📚 Related SDK Examples

These tutorials build on patterns from the SDK's `examples/` directory:

- **Tool Use Basics**: [tool_use_overview.php](../examples/tool_use_overview.php)
- **Tool Implementation**: [tool_use_implementation.php](../examples/tool_use_implementation.php)
- **Extended Thinking**: [extended_thinking.php](../examples/extended_thinking.php)
- **Memory Tool**: [memory_tool.php](../examples/memory_tool.php)
- **Error Handling**: [error_handling.php](../examples/error_handling.php)
- **Token Efficiency**: [token_efficient_tool_use.php](../examples/token_efficient_tool_use.php)

## 🛠️ Shared Utilities

The tutorials use shared helper functions from [helpers.php](./helpers.php) including:

- `runAgentLoop()` - Execute ReAct loops with configurable limits
- `formatToolResult()` - Standardize tool execution results
- `debugAgentStep()` - Visual debugging for agent reasoning
- `manageConversationHistory()` - History management with token limits
- `extractToolUses()` - Parse tool use blocks from responses

## 🎓 Learning Path

### Foundation (Tutorials 0-6)
Follow these in order to build core competency:

```
Tutorial 0 (Concepts)
    ↓
Tutorial 1 (First Agent)
    ↓
Tutorial 2 (ReAct Loop)
    ↓
Tutorial 3 (Multi-Tool)
    ↓
Tutorial 4 (Production)
    ↓
Tutorial 5 (Advanced ReAct)
    ↓
Tutorial 6 (Framework)
```

### Advanced Patterns (Tutorials 7-14)
After completing foundations, explore these specialized patterns:

**Reasoning Patterns:**
- Tutorial 7 (Chain of Thought) - Step-by-step reasoning
- Tutorial 8 (Tree of Thoughts) - Multi-path exploration

**Execution Patterns:**
- Tutorial 9 (Plan-and-Execute) - Systematic task completion
- Tutorial 10 (Reflection) - Self-improvement

**Multi-Agent Patterns:**
- Tutorial 11 (Hierarchical) - Master-worker systems
- Tutorial 12 (Debate) - Consensus building

**Advanced Systems:**
- Tutorial 13 (RAG) - Knowledge integration
- Tutorial 14 (Autonomous) - Self-directed agents
- Tutorial 15 (Context Management) - Advanced context handling
- Tutorial 16 (v0.5.2 Features) - Server tools, auth, stream management
- Tutorial 17 (v0.6.0 Features) - Adaptive thinking, fast mode, new tool suite

### Quick Start Options

If you're already familiar with agents, you can jump to:
- **Tutorial 2** if you understand tool calling
- **Tutorial 4** if you understand ReAct
- **Tutorial 7** for reasoning patterns
- **Tutorial 11** for multi-agent systems

## 💡 Tips for Success

1. **Run the Code**: Each tutorial has executable PHP files. Run them to see agents in action!
2. **Experiment**: Modify the examples, add new tools, change prompts
3. **Read Comments**: The code is heavily commented to explain every decision
4. **Check Costs**: We show token usage - be mindful when experimenting
5. **Debug Reasoning**: Use the debug helpers to understand agent decisions
6. **Start Simple**: Don't skip ahead - foundations matter!

## 🐛 Troubleshooting

### Common Issues

**"API Key not found"**
- Ensure your `.env` file exists in the project root
- Check the key is set: `ANTHROPIC_API_KEY=sk-ant-...`
- Load environment in PHP: `loadEnv(__DIR__ . '/../.env')`

**"Tool not executing"**
- Verify tool name matches exactly
- Check input schema matches the data
- Look at `stop_reason` - should be `tool_use`

**"Agent loops infinitely"**
- Set max iterations (we use 10 by default)
- Check stop conditions in your loop
- Verify tool results are being returned correctly

**"High token usage"**
- Use prompt caching for repeated context
- Limit conversation history length
- Optimize tool descriptions
- See [token_efficient_tool_use.php](../examples/token_efficient_tool_use.php)

### Getting Help

- 📚 [Claude API Documentation](https://docs.claude.com/)
- 🐛 [SDK Issues](https://github.com/claude-php/claude-php-sdk/issues)
- 💬 [SDK Discussions](https://github.com/claude-php/claude-php-sdk/discussions)
- 📖 [Anthropic Discord](https://www.anthropic.com/discord)

## 📖 Further Reading

After completing this series, explore:

- **[Anthropic Prompt Engineering Guide](https://docs.anthropic.com/en/docs/build-with-claude/prompt-engineering/overview)**
- **[Tool Use Best Practices](https://docs.anthropic.com/en/docs/agents-and-tools/tool-use/overview)**
- **[Extended Thinking Documentation](https://docs.anthropic.com/en/docs/build-with-claude/extended-thinking)**
- **Research Papers**:
  - [ReAct: Synergizing Reasoning and Acting in Language Models](https://arxiv.org/abs/2210.03629)
  - [Chain-of-Thought Prompting](https://arxiv.org/abs/2201.11903)

## 📝 Feedback

Found an issue or have suggestions? Please open an issue or PR on the SDK repository!

---

**Ready to start?** → Begin with [Tutorial 0: Introduction to Agentic AI](./00-introduction/)

